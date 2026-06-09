--
-- PostgreSQL database dump
--

-- \restrict vdchEunZwPQTpKmVWmu3EGBWPXbMTo6NIi4Y7EliVTvmzLeaXtzJCJcOleTulGC

-- Dumped from database version 16.14
-- Dumped by pg_dump version 16.14

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: -
--

-- *not* creating schema, since initdb creates it


--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON SCHEMA public IS '';


--
-- Name: btree_gin; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS btree_gin WITH SCHEMA public;


--
-- Name: EXTENSION btree_gin; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION btree_gin IS 'support for indexing common datatypes in GIN';


--
-- Name: pg_trgm; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;


--
-- Name: EXTENSION pg_trgm; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pg_trgm IS 'text similarity measurement and index searching based on trigrams';


--
-- Name: unaccent; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS unaccent WITH SCHEMA public;


--
-- Name: EXTENSION unaccent; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION unaccent IS 'text search dictionary that removes accents';


--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- Name: audit_action; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.audit_action AS ENUM (
    'create',
    'update',
    'delete',
    'publish',
    'submit',
    'approve',
    'reject',
    'login',
    'logout',
    'download',
    'acl_change',
    'password_reset'
);


--
-- Name: dataset_licence; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.dataset_licence AS ENUM (
    'cc_by',
    'cc_by_nc',
    'cc_by_sa',
    'cc_by_nc_sa',
    'cc0',
    'open_data_commons',
    'proprietary',
    'restricted'
);


--
-- Name: language_code; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.language_code AS ENUM (
    'fr',
    'en'
);


--
-- Name: notification_channel; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.notification_channel AS ENUM (
    'email',
    'in_app',
    'both'
);


--
-- Name: notification_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.notification_status AS ENUM (
    'pending',
    'sent',
    'failed',
    'read'
);


--
-- Name: publication_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.publication_status AS ENUM (
    'draft',
    'submitted',
    'under_review',
    'published',
    'archived',
    'rejected'
);


--
-- Name: publication_type; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.publication_type AS ENUM (
    'article',
    'document',
    'event',
    'dataset',
    'news',
    'thesis',
    'report',
    'presentation'
);


--
-- Name: tool_integration_type; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.tool_integration_type AS ENUM (
    'iframe',
    'api',
    'embed'
);


--
-- Name: user_role; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.user_role AS ENUM (
    'visitor',
    'researcher',
    'doctoral_student',
    'partner',
    'axe_admin',
    'super_admin'
);


--
-- Name: user_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.user_status AS ENUM (
    'active',
    'inactive',
    'archived',
    'pending'
);


--
-- Name: visibility; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.visibility AS ENUM (
    'public',
    'partners',
    'internal'
);


--
-- Name: workflow_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.workflow_status AS ENUM (
    'pending',
    'approved',
    'rejected',
    'revision_required'
);


--
-- Name: fn_audit_logs_immutable(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.fn_audit_logs_immutable() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    RAISE EXCEPTION 'Les audit_logs sont immuables — UPDATE et DELETE interdits (RG-018)';
END;
$$;


--
-- Name: fn_audit_publication_statut(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.fn_audit_publication_statut() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF OLD.statut IS DISTINCT FROM NEW.statut THEN
        INSERT INTO audit_logs (
            user_id, user_email, action, ressource_type, ressource_id, details
        )
        VALUES (
            NEW.auteur_id,
            (SELECT email FROM users WHERE id = NEW.auteur_id),
            CASE NEW.statut
                WHEN 'published' THEN 'publish'::audit_action
                WHEN 'submitted' THEN 'submit'::audit_action
                ELSE 'update'::audit_action
            END,
            'publication',
            NEW.id,
            jsonb_build_object(
                'statut_avant', OLD.statut::TEXT,
                'statut_apres', NEW.statut::TEXT,
                'titre', NEW.titre_fr
            )
        );
    END IF;
    RETURN NEW;
END;
$$;


--
-- Name: fn_generate_convention_numero(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.fn_generate_convention_numero() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.numero IS NULL OR NEW.numero = '' THEN
        NEW.numero := 'CONV-' || TO_CHAR(NOW(), 'YYYY') || '-' ||
                      LPAD(nextval('seq_convention_numero')::TEXT, 4, '0');
    END IF;
    RETURN NEW;
END;
$$;


--
-- Name: fn_increment_vues(uuid); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.fn_increment_vues(p_publication_id uuid) RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
    UPDATE publications
    SET nb_vues = nb_vues + 1
    WHERE id = p_publication_id;
END;
$$;


--
-- Name: FUNCTION fn_increment_vues(p_publication_id uuid); Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON FUNCTION public.fn_increment_vues(p_publication_id uuid) IS 'Appeler via background job pour éviter les locks sur les lectures';


--
-- Name: fn_purge_audit_logs(integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.fn_purge_audit_logs(retention_months integer DEFAULT 12) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    deleted_count INTEGER;
BEGIN
    -- Désactivation temporaire du trigger d'immuabilité pour la purge planifiée
    -- Note : cette fonction doit être appelée par un superuser ou un role dédié
    ALTER TABLE audit_logs DISABLE TRIGGER trg_audit_logs_no_delete;

    DELETE FROM audit_logs
    WHERE created_at < NOW() - (retention_months || ' months')::INTERVAL;

    GET DIAGNOSTICS deleted_count = ROW_COUNT;

    ALTER TABLE audit_logs ENABLE TRIGGER trg_audit_logs_no_delete;

    RETURN deleted_count;
END;
$$;


--
-- Name: FUNCTION fn_purge_audit_logs(retention_months integer); Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON FUNCTION public.fn_purge_audit_logs(retention_months integer) IS 'À appeler via Laravel Scheduler — purge les logs de plus de 12 mois';


--
-- Name: fn_set_updated_at(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.fn_set_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$;


--
-- Name: fn_update_nb_publications(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.fn_update_nb_publications() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF TG_OP = 'INSERT' AND NEW.statut = 'published' THEN
        UPDATE profils_chercheurs
        SET nb_publications = nb_publications + 1,
            updated_at = NOW()
        WHERE user_id = NEW.auteur_id;
    ELSIF TG_OP = 'UPDATE' THEN
        IF OLD.statut <> 'published' AND NEW.statut = 'published' THEN
            UPDATE profils_chercheurs
            SET nb_publications = nb_publications + 1,
                updated_at = NOW()
            WHERE user_id = NEW.auteur_id;
        ELSIF OLD.statut = 'published' AND NEW.statut <> 'published' THEN
            UPDATE profils_chercheurs
            SET nb_publications = GREATEST(nb_publications - 1, 0),
                updated_at = NOW()
            WHERE user_id = NEW.auteur_id;
        END IF;
    ELSIF TG_OP = 'DELETE' AND OLD.statut = 'published' THEN
        UPDATE profils_chercheurs
        SET nb_publications = GREATEST(nb_publications - 1, 0),
            updated_at = NOW()
        WHERE user_id = OLD.auteur_id;
    END IF;
    RETURN COALESCE(NEW, OLD);
END;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: actualites; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.actualites (
    publication_id uuid NOT NULL,
    en_une boolean DEFAULT false NOT NULL,
    ordre_une smallint DEFAULT 0 NOT NULL
);


--
-- Name: articles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.articles (
    publication_id uuid NOT NULL,
    doi character varying(200),
    revue character varying(300),
    conference character varying(300),
    volume character varying(50),
    numero character varying(50),
    pages character varying(50),
    annee_publication smallint,
    lien_externe character varying(500),
    indexation text[],
    facteur_impact numeric(6,3),
    peer_reviewed boolean DEFAULT true NOT NULL
);


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid,
    user_email character varying(255),
    user_role public.user_role,
    action public.audit_action NOT NULL,
    ressource_type character varying(50),
    ressource_id uuid,
    details jsonb,
    ip_address inet,
    user_agent text,
    session_id character varying(255),
    succes boolean DEFAULT true NOT NULL,
    message_erreur text,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE audit_logs; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.audit_logs IS 'Journal d''audit immuable — aucun UPDATE ni DELETE — conservation 12 mois minimum';


--
-- Name: axes_thematiques; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.axes_thematiques (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    code character varying(50) NOT NULL,
    nom_fr character varying(200) NOT NULL,
    nom_en character varying(200),
    description_fr text,
    description_en text,
    logo_url character varying(500),
    couleur_hex character(7),
    ordre_affichage smallint DEFAULT 0 NOT NULL,
    actif boolean DEFAULT true NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    responsable_id uuid
);


--
-- Name: TABLE axes_thematiques; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.axes_thematiques IS 'Axes de recherche thématiques du laboratoire UMMISCO';


--
-- Name: chatbot_feedbacks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.chatbot_feedbacks (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    session_id uuid NOT NULL,
    note smallint,
    commentaire text,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chatbot_feedbacks_note_check CHECK (((note >= 1) AND (note <= 5))),
    CONSTRAINT chk_note_range CHECK (((note >= 1) AND (note <= 5)))
);


--
-- Name: chatbot_sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.chatbot_sessions (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid,
    session_token character varying(100) NOT NULL,
    modele_llm character varying(100) DEFAULT 'llama3'::character varying NOT NULL,
    nb_messages integer DEFAULT 0 NOT NULL,
    langue public.language_code DEFAULT 'fr'::public.language_code NOT NULL,
    ip_address inet,
    user_agent text,
    debut_session timestamp with time zone DEFAULT now() NOT NULL,
    fin_session timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE chatbot_sessions; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.chatbot_sessions IS 'Métadonnées de sessions chatbot — les messages ne sont JAMAIS persistés (RG-022)';


--
-- Name: controle_acces; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.controle_acces (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    ressource_type character varying(50) NOT NULL,
    ressource_id uuid NOT NULL,
    groupe character varying(100) NOT NULL,
    permissions text[] DEFAULT '{}'::text[] NOT NULL,
    "accordé_par" uuid,
    date_expiration timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE controle_acces; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.controle_acces IS 'ACL granulaire — contrôle l''accès à chaque ressource par groupe Keycloak';


--
-- Name: COLUMN controle_acces.groupe; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.controle_acces.groupe IS 'Nom du groupe Keycloak (ex: partners_epidemio) ou rôle global';


--
-- Name: conventions_stage; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.conventions_stage (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    numero character varying(50) NOT NULL,
    stagiaire_id uuid,
    stagiaire_nom character varying(200) NOT NULL,
    stagiaire_email character varying(255) NOT NULL,
    encadrant_id uuid,
    axe_id uuid,
    sujet character varying(500) NOT NULL,
    date_debut date NOT NULL,
    date_fin date NOT NULL,
    etablissement character varying(300) NOT NULL,
    statut character varying(50) DEFAULT 'draft'::character varying NOT NULL,
    validee_par uuid,
    validee_le timestamp with time zone,
    document_url character varying(500),
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_stage_dates CHECK ((date_fin > date_debut))
);


--
-- Name: datasets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.datasets (
    publication_id uuid NOT NULL,
    licence public.dataset_licence NOT NULL,
    format_principal character varying(50) NOT NULL,
    formats_disponibles text[],
    taille_totale_mo numeric(12,2),
    version character varying(20) DEFAULT '1.0'::character varying NOT NULL,
    doi character varying(200),
    metadonnees jsonb,
    periode_collecte_debut date,
    periode_collecte_fin date,
    zone_geographique character varying(300),
    methodologie text,
    conditions_acces text,
    lien_externe character varying(500),
    CONSTRAINT chk_taille_positive CHECK (((taille_totale_mo IS NULL) OR (taille_totale_mo >= (0)::numeric))),
    CONSTRAINT chk_version_format CHECK (((version)::text ~ '^\d+\.\d+(\.\d+)?$'::text))
);


--
-- Name: COLUMN datasets.metadonnees; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.datasets.metadonnees IS 'Métadonnées libres au format JSONB — ex: {"variables": ["temp","hum"], "capteurs": 12}';


--
-- Name: datasets_fichiers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.datasets_fichiers (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    dataset_id uuid NOT NULL,
    nom character varying(300) NOT NULL,
    description text,
    chemin_minio character varying(500) NOT NULL,
    bucket_minio character varying(100) DEFAULT 'datasets'::character varying NOT NULL,
    taille_octets bigint,
    format character varying(50),
    checksum_sha256 character(64),
    version character varying(20),
    est_principal boolean DEFAULT false NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: datasets_versions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.datasets_versions (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    dataset_id uuid NOT NULL,
    version character varying(20) NOT NULL,
    notes_version text,
    cree_par uuid,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: demandes_collaboration; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.demandes_collaboration (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    demandeur_id uuid,
    nom_externe character varying(200),
    email_externe character varying(255),
    organisation character varying(200),
    description text NOT NULL,
    type_collab character varying(100),
    axe_cible_id uuid,
    statut character varying(50) DEFAULT 'pending'::character varying NOT NULL,
    traite_par uuid,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: demandes_contact; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.demandes_contact (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    nom character varying(200) NOT NULL,
    email character varying(255) NOT NULL,
    organisation character varying(200),
    sujet character varying(300) NOT NULL,
    message text NOT NULL,
    type_demande character varying(50) DEFAULT 'contact'::character varying NOT NULL,
    axe_concerne_id uuid,
    traite boolean DEFAULT false NOT NULL,
    traite_par uuid,
    traite_le timestamp with time zone,
    notes_admin text,
    ip_address inet,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: demandes_suppression; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.demandes_suppression (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    publication_id uuid NOT NULL,
    propose_par uuid NOT NULL,
    motif text NOT NULL,
    statut character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: documents; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.documents (
    publication_id uuid NOT NULL,
    fichier_url character varying(500) NOT NULL,
    fichier_nom character varying(300),
    fichier_taille bigint,
    fichier_mime character varying(100),
    nb_pages integer,
    these_soutenue boolean DEFAULT false NOT NULL,
    date_soutenance date,
    jury_membres text[]
);


--
-- Name: evenements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.evenements (
    publication_id uuid NOT NULL,
    date_debut timestamp with time zone NOT NULL,
    date_fin timestamp with time zone,
    lieu character varying(300),
    lieu_details text,
    format character varying(50),
    lien_inscription character varying(500),
    lien_visio character varying(500),
    capacite_max integer,
    nb_inscrits integer DEFAULT 0 NOT NULL,
    programme text,
    intervenants text[],
    CONSTRAINT chk_evenement_dates CHECK (((date_fin IS NULL) OR (date_fin >= date_debut)))
);


--
-- Name: medias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.medias (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    nom character varying(300) NOT NULL,
    alt_text character varying(300),
    chemin_minio character varying(500) NOT NULL,
    bucket_minio character varying(100) DEFAULT 'medias'::character varying NOT NULL,
    url_publique character varying(500),
    mime_type character varying(100) NOT NULL,
    taille_octets bigint,
    largeur_px integer,
    hauteur_px integer,
    owner_id uuid,
    reference_type character varying(50),
    reference_id uuid,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: newsletter_abonnes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.newsletter_abonnes (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    email character varying(255) NOT NULL,
    nom character varying(100),
    langue public.language_code DEFAULT 'fr'::public.language_code NOT NULL,
    actif boolean DEFAULT true NOT NULL,
    token_unsub character varying(100),
    subscribed_at timestamp with time zone DEFAULT now() NOT NULL,
    unsubscribed_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notifications (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    destinataire_id uuid NOT NULL,
    type character varying(100) NOT NULL,
    canal public.notification_channel DEFAULT 'email'::public.notification_channel NOT NULL,
    statut public.notification_status DEFAULT 'pending'::public.notification_status NOT NULL,
    sujet character varying(300),
    contenu text,
    contenu_html text,
    metadonnees jsonb,
    reference_type character varying(50),
    reference_id uuid,
    tentatives smallint DEFAULT 0 NOT NULL,
    derniere_tentative timestamp with time zone,
    envoyee_le timestamp with time zone,
    lue_le timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: notifications_templates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notifications_templates (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    code character varying(100) NOT NULL,
    langue public.language_code DEFAULT 'fr'::public.language_code NOT NULL,
    type character varying(100) NOT NULL,
    sujet character varying(300),
    corps_text text,
    corps_html text,
    actif boolean DEFAULT true NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: outils_doctoraux; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.outils_doctoraux (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    nom character varying(200) NOT NULL,
    description_fr text,
    description_en text,
    type_integration public.tool_integration_type DEFAULT 'iframe'::public.tool_integration_type NOT NULL,
    url_integration character varying(500) NOT NULL,
    doctorant_id uuid,
    axe_id uuid,
    actif boolean DEFAULT true NOT NULL,
    requiert_auth boolean DEFAULT false NOT NULL,
    hauteur_iframe integer DEFAULT 600,
    largeur_iframe integer DEFAULT 100,
    parametres jsonb,
    ordre_affichage smallint DEFAULT 0 NOT NULL,
    domaines_autorises text[],
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE outils_doctoraux; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.outils_doctoraux IS 'Outils externes intégrés via iframe (Evelop, outil carbone, capteurs)';


--
-- Name: parametres_systeme; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.parametres_systeme (
    cle character varying(100) NOT NULL,
    valeur text NOT NULL,
    description text,
    modifiable boolean DEFAULT true NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE parametres_systeme; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.parametres_systeme IS 'Configuration globale du portail (clé-valeur)';


--
-- Name: profils_chercheurs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.profils_chercheurs (
    user_id uuid NOT NULL,
    specialite character varying(200),
    domaines_expertise text[],
    h_index smallint,
    nb_publications integer DEFAULT 0 NOT NULL,
    date_entree_labo date,
    statut_chercheur character varying(50),
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: profils_doctorants; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.profils_doctorants (
    user_id uuid NOT NULL,
    directeur_id uuid,
    co_directeur_id uuid,
    titre_these character varying(500),
    date_inscription date,
    date_soutenance_prev date,
    date_soutenance_eff date,
    ecole_doctorale character varying(200),
    financement character varying(200),
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    domaine_expertise character varying(200)
);


--
-- Name: profils_partenaires; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.profils_partenaires (
    user_id uuid NOT NULL,
    organisation character varying(200) NOT NULL,
    pays character varying(100),
    domaine_acces character varying(200),
    date_debut_partenariat date,
    date_fin_partenariat date,
    contact_referent character varying(200),
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: publications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.publications (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    titre_fr character varying(500) NOT NULL,
    titre_en character varying(500),
    resume_fr text,
    resume_en text,
    type public.publication_type NOT NULL,
    statut public.publication_status DEFAULT 'draft'::public.publication_status NOT NULL,
    visibilite public.visibility DEFAULT 'public'::public.visibility NOT NULL,
    langue_principale public.language_code DEFAULT 'fr'::public.language_code NOT NULL,
    auteur_id uuid NOT NULL,
    axe_id uuid,
    mots_cles text[],
    image_couverture_url character varying(500),
    nb_vues integer DEFAULT 0 NOT NULL,
    nb_telechargements integer DEFAULT 0 NOT NULL,
    date_publication timestamp with time zone,
    date_soumission timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    fts_fr tsvector GENERATED ALWAYS AS ((setweight(to_tsvector('french'::regconfig, (COALESCE(titre_fr, ''::character varying))::text), 'A'::"char") || setweight(to_tsvector('french'::regconfig, COALESCE(resume_fr, ''::text)), 'B'::"char"))) STORED,
    fts_en tsvector GENERATED ALWAYS AS ((setweight(to_tsvector('english'::regconfig, (COALESCE(titre_en, ''::character varying))::text), 'A'::"char") || setweight(to_tsvector('english'::regconfig, COALESCE(resume_en, ''::text)), 'B'::"char"))) STORED,
    CONSTRAINT chk_nb_dl_positive CHECK ((nb_telechargements >= 0)),
    CONSTRAINT chk_nb_vues_positive CHECK ((nb_vues >= 0))
);


--
-- Name: TABLE publications; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.publications IS 'Table centrale — tous les contenus publiés ou en cours';


--
-- Name: COLUMN publications.fts_fr; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.publications.fts_fr IS 'Index full-text français — généré automatiquement';


--
-- Name: COLUMN publications.fts_en; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.publications.fts_en IS 'Index full-text anglais — généré automatiquement';


--
-- Name: publications_auteurs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.publications_auteurs (
    publication_id uuid NOT NULL,
    user_id uuid NOT NULL,
    ordre smallint DEFAULT 1 NOT NULL,
    auteur_externe boolean DEFAULT false NOT NULL,
    nom_externe character varying(200),
    affiliation_externe character varying(300),
    CONSTRAINT chk_auteur_externe CHECK (((auteur_externe = false) OR (nom_externe IS NOT NULL)))
);


--
-- Name: publications_medias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.publications_medias (
    publication_id uuid NOT NULL,
    media_id uuid NOT NULL,
    role character varying(50) DEFAULT 'attachment'::character varying NOT NULL,
    ordre smallint DEFAULT 0 NOT NULL
);


--
-- Name: publications_tags; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.publications_tags (
    publication_id uuid NOT NULL,
    tag_id uuid NOT NULL
);


--
-- Name: seq_convention_numero; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.seq_convention_numero
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tags; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tags (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    nom_fr character varying(100) NOT NULL,
    nom_en character varying(100),
    slug character varying(120) NOT NULL
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    keycloak_id character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    nom character varying(100) NOT NULL,
    prenom character varying(100) NOT NULL,
    role public.user_role DEFAULT 'visitor'::public.user_role NOT NULL,
    statut public.user_status DEFAULT 'active'::public.user_status NOT NULL,
    axe_principal_id uuid,
    photo_url character varying(500),
    biographie_fr text,
    biographie_en text,
    titre_academique character varying(100),
    grade character varying(100),
    orcid_id character varying(50),
    page_web_url character varying(500),
    linkedin_url character varying(500),
    researchgate_url character varying(500),
    langue_preference public.language_code DEFAULT 'fr'::public.language_code NOT NULL,
    email_notifications boolean DEFAULT true NOT NULL,
    derniere_connexion timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    password character varying(255)
);


--
-- Name: TABLE users; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.users IS 'Tous les utilisateurs du portail (chercheurs, doctorants, partenaires, admins)';


--
-- Name: COLUMN users.keycloak_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.keycloak_id IS 'Identifiant unique dans Keycloak — source de vérité pour l''authentification';


--
-- Name: COLUMN users.deleted_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.deleted_at IS 'Soft delete : le compte est désactivé mais les publications restent';


--
-- Name: users_axes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users_axes (
    user_id uuid NOT NULL,
    axe_id uuid NOT NULL,
    role_dans_axe character varying(50),
    depuis date
);


--
-- Name: v_datasets_catalogue; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.v_datasets_catalogue AS
 SELECT p.id,
    p.titre_fr,
    p.titre_en,
    p.resume_fr,
    p.visibilite,
    p.date_publication,
    d.licence,
    d.format_principal,
    d.formats_disponibles,
    d.taille_totale_mo,
    d.version,
    d.doi,
    d.metadonnees,
    d.periode_collecte_debut,
    d.periode_collecte_fin,
    d.zone_geographique,
    u.nom AS auteur_nom,
    u.prenom AS auteur_prenom,
    a.nom_fr AS axe_nom_fr,
    a.code AS axe_code
   FROM (((public.publications p
     JOIN public.datasets d ON ((d.publication_id = p.id)))
     JOIN public.users u ON ((u.id = p.auteur_id)))
     LEFT JOIN public.axes_thematiques a ON ((a.id = p.axe_id)))
  WHERE ((p.statut = 'published'::public.publication_status) AND (p.deleted_at IS NULL));


--
-- Name: v_membres_actifs; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.v_membres_actifs AS
 SELECT u.id,
    u.nom,
    u.prenom,
    u.email,
    u.role,
    u.titre_academique,
    u.grade,
    u.orcid_id,
    u.photo_url,
    u.biographie_fr,
    u.biographie_en,
    u.page_web_url,
    u.langue_preference,
    a.id AS axe_principal_id,
    a.nom_fr AS axe_nom_fr,
    a.code AS axe_code,
    pc.specialite,
    pc.domaines_expertise,
    pc.h_index,
    pc.nb_publications
   FROM ((public.users u
     LEFT JOIN public.axes_thematiques a ON ((a.id = u.axe_principal_id)))
     LEFT JOIN public.profils_chercheurs pc ON ((pc.user_id = u.id)))
  WHERE ((u.statut = 'active'::public.user_status) AND (u.deleted_at IS NULL) AND (u.role = ANY (ARRAY['researcher'::public.user_role, 'doctoral_student'::public.user_role, 'axe_admin'::public.user_role])));


--
-- Name: v_publications_publiques; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.v_publications_publiques AS
 SELECT p.id,
    p.titre_fr,
    p.titre_en,
    p.resume_fr,
    p.resume_en,
    p.type,
    p.langue_principale,
    p.mots_cles,
    p.image_couverture_url,
    p.nb_vues,
    p.nb_telechargements,
    p.date_publication,
    u.id AS auteur_id,
    u.nom AS auteur_nom,
    u.prenom AS auteur_prenom,
    u.photo_url AS auteur_photo,
    a.id AS axe_id,
    a.nom_fr AS axe_nom_fr,
    a.nom_en AS axe_nom_en,
    a.code AS axe_code
   FROM ((public.publications p
     JOIN public.users u ON ((u.id = p.auteur_id)))
     LEFT JOIN public.axes_thematiques a ON ((a.id = p.axe_id)))
  WHERE ((p.statut = 'published'::public.publication_status) AND (p.visibilite = 'public'::public.visibility) AND (p.deleted_at IS NULL));


--
-- Name: VIEW v_publications_publiques; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON VIEW public.v_publications_publiques IS 'Publications publiées et publiques — utilisée par le portail public';


--
-- Name: workflow_validations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workflow_validations (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    publication_id uuid NOT NULL,
    soumetteur_id uuid NOT NULL,
    validateur_id uuid,
    statut public.workflow_status DEFAULT 'pending'::public.workflow_status NOT NULL,
    commentaire_admin text,
    commentaire_auteur text,
    version smallint DEFAULT 1 NOT NULL,
    date_soumission timestamp with time zone DEFAULT now() NOT NULL,
    date_decision timestamp with time zone,
    date_limite timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE workflow_validations; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.workflow_validations IS 'Historique complet des cycles de validation pour les soumissions doctorants';


--
-- Name: COLUMN workflow_validations.version; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.workflow_validations.version IS 'Incrémenté à chaque nouvelle soumission après correction';


--
-- Name: v_soumissions_en_attente; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.v_soumissions_en_attente AS
 SELECT wv.id AS workflow_id,
    wv.publication_id,
    wv.date_soumission,
    wv.version,
    p.titre_fr,
    p.type,
    p.axe_id,
    a.nom_fr AS axe_nom,
    a.code AS axe_code,
    u.nom AS soumetteur_nom,
    u.prenom AS soumetteur_prenom,
    u.email AS soumetteur_email
   FROM (((public.workflow_validations wv
     JOIN public.publications p ON ((p.id = wv.publication_id)))
     JOIN public.users u ON ((u.id = wv.soumetteur_id)))
     LEFT JOIN public.axes_thematiques a ON ((a.id = p.axe_id)))
  WHERE (wv.statut = 'pending'::public.workflow_status);


--
-- Name: v_statistiques_laboratoire; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.v_statistiques_laboratoire AS
 SELECT ( SELECT count(*) AS count
           FROM public.publications
          WHERE ((publications.statut = 'published'::public.publication_status) AND (publications.deleted_at IS NULL))) AS total_publications,
    ( SELECT count(*) AS count
           FROM (public.datasets d
             JOIN public.publications p ON ((p.id = d.publication_id)))
          WHERE ((p.statut = 'published'::public.publication_status) AND (p.deleted_at IS NULL))) AS total_datasets,
    ( SELECT count(*) AS count
           FROM public.users
          WHERE ((users.statut = 'active'::public.user_status) AND (users.role = 'researcher'::public.user_role) AND (users.deleted_at IS NULL))) AS total_chercheurs,
    ( SELECT count(*) AS count
           FROM public.users
          WHERE ((users.statut = 'active'::public.user_status) AND (users.role = 'doctoral_student'::public.user_role) AND (users.deleted_at IS NULL))) AS total_doctorants,
    ( SELECT count(*) AS count
           FROM public.axes_thematiques
          WHERE (axes_thematiques.actif = true)) AS total_axes,
    ( SELECT count(*) AS count
           FROM public.outils_doctoraux
          WHERE (outils_doctoraux.actif = true)) AS total_outils_doctoraux,
    ( SELECT sum(publications.nb_vues) AS sum
           FROM public.publications
          WHERE ((publications.statut = 'published'::public.publication_status) AND (publications.deleted_at IS NULL))) AS total_vues,
    ( SELECT sum(publications.nb_telechargements) AS sum
           FROM public.publications
          WHERE ((publications.statut = 'published'::public.publication_status) AND (publications.deleted_at IS NULL))) AS total_telechargements;


--
-- Name: votes_suppression; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.votes_suppression (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    demande_suppression_id uuid NOT NULL,
    user_id uuid NOT NULL,
    daccord boolean NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: workflow_historique; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workflow_historique (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workflow_id uuid NOT NULL,
    statut_avant public.workflow_status,
    statut_apres public.workflow_status NOT NULL,
    commentaire text,
    acteur_id uuid,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Data for Name: actualites; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: articles; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: audit_logs; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.audit_logs VALUES ('a1f63b59-556c-4f92-ac78-86207dbf9734', 'a1f63b58-d148-4396-a293-83e8b69d271b', 'mock_super_admin@ummisco.ucad.sn', 'super_admin', 'login', 'user', 'a1f63b58-d148-4396-a293-83e8b69d271b', '{"mode": "mock", "role": "super_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'j2q1pPy3bV7njUg7q18ugKHsweFHGCu4JpEreNxL', true, NULL, '2026-06-06 23:41:37+00');
INSERT INTO public.audit_logs VALUES ('a1f63b5a-35fd-4f90-b3bc-a544a865eeea', 'a1f63b58-d148-4396-a293-83e8b69d271b', 'mock_super_admin@ummisco.ucad.sn', 'super_admin', 'login', 'user', 'a1f63b58-d148-4396-a293-83e8b69d271b', '{"mode": "mock", "role": "super_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'j2q1pPy3bV7njUg7q18ugKHsweFHGCu4JpEreNxL', true, NULL, '2026-06-06 23:41:37+00');
INSERT INTO public.audit_logs VALUES ('a1f63bbe-4ddf-4ed8-8bcb-09441438016a', 'a1f63b58-d148-4396-a293-83e8b69d271b', 'mock_super_admin@ummisco.ucad.sn', 'super_admin', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'j2q1pPy3bV7njUg7q18ugKHsweFHGCu4JpEreNxL', true, NULL, '2026-06-06 23:42:43+00');
INSERT INTO public.audit_logs VALUES ('a1f63bf2-f0c0-4f42-8198-cfe475e8ba3d', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'gpH6g5XyoE8Yf0gTO5fLa1d7ZaoanZ5x2yzoTGTN', true, NULL, '2026-06-06 23:43:18+00');
INSERT INTO public.audit_logs VALUES ('a1f794bd-c0e3-4cf1-a878-2896caf184f1', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '12XRzEX1C44kM52216lVcdSHTvR42uMDPfcnoPlO', true, NULL, '2026-06-07 15:47:25+00');
INSERT INTO public.audit_logs VALUES ('a1f7956d-9e6e-474a-8bed-e2eeb9e7af03', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'cViLb3bQ2M9VbKkXPSzRXKM5ZtghGeuyV4WWn3Gy', true, NULL, '2026-06-07 15:49:20+00');
INSERT INTO public.audit_logs VALUES ('a1f7965a-5dfb-4923-bbab-0aa5f402a9bd', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '12XRzEX1C44kM52216lVcdSHTvR42uMDPfcnoPlO', true, NULL, '2026-06-07 15:51:55+00');
INSERT INTO public.audit_logs VALUES ('a1f7966c-87f3-43c1-be33-30f290660d2e', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'cViLb3bQ2M9VbKkXPSzRXKM5ZtghGeuyV4WWn3Gy', true, NULL, '2026-06-07 15:52:07+00');
INSERT INTO public.audit_logs VALUES ('a1f7966c-97f0-44d0-ab86-61135f51b81b', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'cViLb3bQ2M9VbKkXPSzRXKM5ZtghGeuyV4WWn3Gy', true, NULL, '2026-06-07 15:52:07+00');
INSERT INTO public.audit_logs VALUES ('a1f79670-920b-4b92-b581-1941d1c05b64', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'cViLb3bQ2M9VbKkXPSzRXKM5ZtghGeuyV4WWn3Gy', true, NULL, '2026-06-07 15:52:10+00');
INSERT INTO public.audit_logs VALUES ('a1f79693-2e2c-4dab-8a2e-a62f789bdeaf', 'a1f63b58-d148-4396-a293-83e8b69d271b', 'mock_super_admin@ummisco.ucad.sn', 'super_admin', 'login', 'user', 'a1f63b58-d148-4396-a293-83e8b69d271b', '{"mode": "mock", "role": "super_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'cViLb3bQ2M9VbKkXPSzRXKM5ZtghGeuyV4WWn3Gy', true, NULL, '2026-06-07 15:52:32+00');
INSERT INTO public.audit_logs VALUES ('a1f79696-4280-4bb5-8cbf-ebf3426cae45', 'a1f63b58-d148-4396-a293-83e8b69d271b', 'mock_super_admin@ummisco.ucad.sn', 'super_admin', 'login', 'user', 'a1f63b58-d148-4396-a293-83e8b69d271b', '{"mode": "mock", "role": "super_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'cViLb3bQ2M9VbKkXPSzRXKM5ZtghGeuyV4WWn3Gy', true, NULL, '2026-06-07 15:52:34+00');
INSERT INTO public.audit_logs VALUES ('a1f796ab-a4c5-41e6-8d8b-dc74affd45e2', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'lM10bDtRFBA4i66gqbRpVWfUdemh3poYcq26fDB4', true, NULL, '2026-06-07 15:52:48+00');
INSERT INTO public.audit_logs VALUES ('a1f79985-affd-40f3-a13f-278b42c448a0', 'a1f63b58-d148-4396-a293-83e8b69d271b', 'mock_super_admin@ummisco.ucad.sn', 'super_admin', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'cViLb3bQ2M9VbKkXPSzRXKM5ZtghGeuyV4WWn3Gy', true, NULL, '2026-06-07 16:00:47+00');
INSERT INTO public.audit_logs VALUES ('a1f799ab-f92d-4012-859e-b915e629efad', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'lM10bDtRFBA4i66gqbRpVWfUdemh3poYcq26fDB4', true, NULL, '2026-06-07 16:01:12+00');
INSERT INTO public.audit_logs VALUES ('a1f799d5-2aa0-40ff-8cd2-9329c3dcb915', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'zK1hnZIXVmv8DN1y6L3wIkyvX2vgjQcm4WWdeJM7', true, NULL, '2026-06-07 16:01:39+00');
INSERT INTO public.audit_logs VALUES ('a1f79a3f-9e8d-4ac7-a9a0-e61cdbb1e9d2', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'g9c47N73uYaAqaL3dCe2eaFgR7S4mZB44zkz37kJ', true, NULL, '2026-06-07 16:02:49+00');
INSERT INTO public.audit_logs VALUES ('a1f79a9d-d746-4068-9ea4-15c59b188bf8', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'g9c47N73uYaAqaL3dCe2eaFgR7S4mZB44zkz37kJ', true, NULL, '2026-06-07 16:03:50+00');
INSERT INTO public.audit_logs VALUES ('a1f79b58-0a00-4946-b343-b0efa69497c1', 'a1f79b56-5960-49e4-a23e-f68e83df330d', 'mock_doctoral_student@ummisco.ucad.sn', 'doctoral_student', 'login', 'user', 'a1f79b56-5960-49e4-a23e-f68e83df330d', '{"mode": "mock", "role": "doctoral_student"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'T8F5Mp7aQD0VOgnsBW91ji9KB8fRunEpkEHVIvNb', true, NULL, '2026-06-07 16:05:52+00');
INSERT INTO public.audit_logs VALUES ('a1f7a1b5-2712-4151-b1c5-fa1d8305587c', 'a1f79b56-5960-49e4-a23e-f68e83df330d', 'mock_doctoral_student@ummisco.ucad.sn', 'doctoral_student', 'submit', 'publication', 'a1f7a1b3-1c51-4923-981e-af900aec2d51', '{"titre": "Modelisation mathematique du paludisme au Senegal", "axe_id": "39a77ba8-73cb-4a73-979b-fb532d0b99e5"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'T8F5Mp7aQD0VOgnsBW91ji9KB8fRunEpkEHVIvNb', true, NULL, '2026-06-07 16:23:39+00');
INSERT INTO public.audit_logs VALUES ('a1f7a589-a444-4282-89a0-0e2d89b34fd1', 'a1f79b56-5960-49e4-a23e-f68e83df330d', 'mock_doctoral_student@ummisco.ucad.sn', 'doctoral_student', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'T8F5Mp7aQD0VOgnsBW91ji9KB8fRunEpkEHVIvNb', true, NULL, '2026-06-07 16:34:23+00');
INSERT INTO public.audit_logs VALUES ('a1f7a648-ea21-48c6-8790-285434b89e60', 'a1f63b58-d148-4396-a293-83e8b69d271b', 'mock_super_admin@ummisco.ucad.sn', 'super_admin', 'login', 'user', 'a1f63b58-d148-4396-a293-83e8b69d271b', '{"mode": "mock", "role": "super_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'v0OJhPbRkOgdEfq6d2ZBBTwtTJcyKjGTv7VYeJbE', true, NULL, '2026-06-07 16:36:28+00');
INSERT INTO public.audit_logs VALUES ('a1f7abe3-c38e-4c48-b6cd-1a666886b7a3', 'a1f63b58-d148-4396-a293-83e8b69d271b', 'mock_super_admin@ummisco.ucad.sn', 'super_admin', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'v0OJhPbRkOgdEfq6d2ZBBTwtTJcyKjGTv7VYeJbE', true, NULL, '2026-06-07 16:52:09+00');
INSERT INTO public.audit_logs VALUES ('a1f7ac4e-662d-48a0-afd0-0c7b5f8c0f3f', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '6w7rw1Kl44SYRt2iiEiecKssbJFI2RgaH2N5A2ch', true, NULL, '2026-06-07 16:53:18+00');
INSERT INTO public.audit_logs VALUES ('a1f7add7-6013-44eb-b93c-b4a65952641c', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'publish', 'publication', 'a1f7add5-a3c2-4adc-86e6-5d8ffc2cdca0', '{"mode": "direct", "titre": "Document de Recherche UMMISCO"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '6w7rw1Kl44SYRt2iiEiecKssbJFI2RgaH2N5A2ch', true, NULL, '2026-06-07 16:57:35+00');
INSERT INTO public.audit_logs VALUES ('a1f7af58-ae14-405b-acef-228ca9f7836b', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'zK1hnZIXVmv8DN1y6L3wIkyvX2vgjQcm4WWdeJM7', true, NULL, '2026-06-07 17:01:49+00');
INSERT INTO public.audit_logs VALUES ('a1f7b054-8f98-497d-9443-e1aebe0cf806', 'a1f79b56-5960-49e4-a23e-f68e83df330d', 'mock_doctoral_student@ummisco.ucad.sn', 'partner', 'login', 'user', 'a1f79b56-5960-49e4-a23e-f68e83df330d', '{"mode": "mock", "role": "doctoral_student"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'a5MyLIhek9U9w8sF76emKeWKmvDQYJpmewqzdMEc', true, NULL, '2026-06-07 17:04:34+00');
INSERT INTO public.audit_logs VALUES ('a1f90803-d568-4561-99f8-62e1c50cff7c', 'a1f79b56-5960-49e4-a23e-f68e83df330d', 'mock_doctoral_student@ummisco.ucad.sn', 'partner', 'login', 'user', 'a1f79b56-5960-49e4-a23e-f68e83df330d', '{"mode": "mock", "role": "doctoral_student"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'DDaqkLiTQpWbY5ztwruqYC0LLulnmImgp6gfjsj7', true, NULL, '2026-06-08 09:05:34+00');
INSERT INTO public.audit_logs VALUES ('a1f90808-ac23-4c7b-98e6-7dfaf9b469fb', 'a1f79b56-5960-49e4-a23e-f68e83df330d', 'mock_doctoral_student@ummisco.ucad.sn', 'partner', 'login', 'user', 'a1f79b56-5960-49e4-a23e-f68e83df330d', '{"mode": "mock", "role": "doctoral_student"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'DDaqkLiTQpWbY5ztwruqYC0LLulnmImgp6gfjsj7', true, NULL, '2026-06-08 09:05:37+00');
INSERT INTO public.audit_logs VALUES ('a1f920fa-5c99-47a6-a13d-d16b4e43f72c', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'iEeL2InG0FAfE47C3ogcWlBDwVnrZ3D5K6X4SxGr', true, NULL, '2026-06-08 10:15:22+00');
INSERT INTO public.audit_logs VALUES ('a1f92a6c-46e8-46d8-8e59-94d9badeb001', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'login', 'user', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '{"mode": "mock", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sKKEdDnFJADl08VNkKvMWwEaW57Bg29Q6dBhip2g', true, NULL, '2026-06-08 10:41:47+00');
INSERT INTO public.audit_logs VALUES ('a1f92d76-ed58-4ad9-ae36-d0134585fa0f', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sKKEdDnFJADl08VNkKvMWwEaW57Bg29Q6dBhip2g', true, NULL, '2026-06-08 10:50:17+00');
INSERT INTO public.audit_logs VALUES ('a1f92f5e-9858-473c-9582-7659b5361edd', 'a1f79b56-5960-49e4-a23e-f68e83df330d', 'mock_doctoral_student@ummisco.ucad.sn', 'partner', 'login', 'user', 'a1f79b56-5960-49e4-a23e-f68e83df330d', '{"mode": "mock", "role": "doctoral_student"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '0GkdGfuhiET25SoMwc24dUMRiJf2sFJZXYFxgNJN', true, NULL, '2026-06-08 10:55:36+00');
INSERT INTO public.audit_logs VALUES ('a1f931c7-be86-4745-a233-381ef6733991', 'a1f79b56-5960-49e4-a23e-f68e83df330d', 'mock_doctoral_student@ummisco.ucad.sn', 'partner', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '0GkdGfuhiET25SoMwc24dUMRiJf2sFJZXYFxgNJN', true, NULL, '2026-06-08 11:02:21+00');
INSERT INTO public.audit_logs VALUES ('a1f93ec9-967c-4178-ae52-09fd09a99462', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock_researcher@ummisco.ucad.sn', 'researcher', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'iEeL2InG0FAfE47C3ogcWlBDwVnrZ3D5K6X4SxGr', true, NULL, '2026-06-08 11:38:43+00');
INSERT INTO public.audit_logs VALUES ('a1f971e0-0bd3-49d2-8a3d-714f1f22f039', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'login', 'user', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', '{"mode": "keycloak_direct", "role": "super_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'zwAMiygPFpuPCWmb1vU226nGooUMPyhaQSqqCwT5', true, NULL, '2026-06-08 14:01:34+00');
INSERT INTO public.audit_logs VALUES ('6c2ce3f0-3ce4-4215-b2b6-72dc030ec6c9', 'a1f79b56-5960-49e4-a23e-f68e83df330d', 'mock_doctoral_student@ummisco.ucad.sn', NULL, 'publish', 'publication', 'a1f7a1b3-1c51-4923-981e-af900aec2d51', '{"titre": "Modelisation mathematique du paludisme au Senegal", "statut_apres": "published", "statut_avant": "submitted"}', NULL, NULL, NULL, true, NULL, '2026-06-08 14:03:32.002507+00');
INSERT INTO public.audit_logs VALUES ('a1f97294-5415-48e7-828d-5e3b8ae222e3', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'approve', 'publication', 'a1f7a1b3-1c51-4923-981e-af900aec2d51', '{"titre": "Modelisation mathematique du paludisme au Senegal", "workflow_id": "a1f7a1b4-c9dc-4daf-b09a-9b7f95bd8238"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'zwAMiygPFpuPCWmb1vU226nGooUMPyhaQSqqCwT5', true, NULL, '2026-06-08 14:03:32+00');
INSERT INTO public.audit_logs VALUES ('a1f98141-d5a4-48a3-b309-8a187c302dfd', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'login', 'user', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', '{"mode": "keycloak_direct", "role": "super_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'EBAX3SjViIVPCiVf9R19kwQCwmD4YsfXhMwYXenK', true, NULL, '2026-06-08 14:44:35+00');
INSERT INTO public.audit_logs VALUES ('a1fa27f8-9f0b-4972-b99d-c2cb8cb3bc3d', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'login', 'user', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', '{"mode": "keycloak_direct", "role": "super_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'XTWcuz37NOPPJoFs0yEiR4PuFblxBXqYqrdLJVVl', true, NULL, '2026-06-08 22:30:45+00');
INSERT INTO public.audit_logs VALUES ('a1fa2a56-75b7-419c-962b-af3f14b80dec', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'XTWcuz37NOPPJoFs0yEiR4PuFblxBXqYqrdLJVVl', true, NULL, '2026-06-08 22:37:22+00');
INSERT INTO public.audit_logs VALUES ('a1fa2a8a-1166-4741-a841-338d0bc09c3a', 'a1fa27b0-32b2-4606-8932-626ba3e94f55', 'papawade183@gmail.com', 'researcher', 'login', 'user', 'a1fa27b0-32b2-4606-8932-626ba3e94f55', '{"mode": "keycloak_direct", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '7J5AA54Pu6qseAguDbDFTitqToGEd50zjAdeTCTN', true, NULL, '2026-06-08 22:37:55+00');
INSERT INTO public.audit_logs VALUES ('a1fa2a9e-a1bf-47b7-a69b-54cc614a20ae', 'a1fa27b0-32b2-4606-8932-626ba3e94f55', 'papawade183@gmail.com', 'researcher', 'login', 'user', 'a1fa27b0-32b2-4606-8932-626ba3e94f55', '{"mode": "keycloak_direct", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '7J5AA54Pu6qseAguDbDFTitqToGEd50zjAdeTCTN', true, NULL, '2026-06-08 22:38:09+00');
INSERT INTO public.audit_logs VALUES ('a1fa4d3a-27d9-4dc5-8092-db34e319a893', 'a1fa27b0-32b2-4606-8932-626ba3e94f55', 'papawade183@gmail.com', 'researcher', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '7J5AA54Pu6qseAguDbDFTitqToGEd50zjAdeTCTN', true, NULL, '2026-06-09 00:14:56+00');
INSERT INTO public.audit_logs VALUES ('a1fa4d5e-5051-498e-9b87-c319bdc8516a', 'a1fa27b0-32b2-4606-8932-626ba3e94f55', 'papawade183@gmail.com', 'researcher', 'login', 'user', 'a1fa27b0-32b2-4606-8932-626ba3e94f55', '{"mode": "keycloak_direct", "role": "researcher"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'V9S3IKahPbDvTp6rBLVCumHx3UKKQUEstQ9steGW', true, NULL, '2026-06-09 00:15:19+00');
INSERT INTO public.audit_logs VALUES ('a1fa4f18-b74d-47a0-9aba-1dd43394cbd8', 'a1fa27b0-32b2-4606-8932-626ba3e94f55', 'papawade183@gmail.com', 'researcher', 'publish', 'publication', 'a1fa4f02-e72a-449f-bd06-6a71c78b694a', '{"mode": "direct", "titre": "Rapport de recherche"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'V9S3IKahPbDvTp6rBLVCumHx3UKKQUEstQ9steGW', true, NULL, '2026-06-09 00:20:09+00');
INSERT INTO public.audit_logs VALUES ('a1fa5107-6100-49f4-802a-8b6ef6cb7917', 'a1fa27b0-32b2-4606-8932-626ba3e94f55', 'papawade183@gmail.com', 'researcher', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'V9S3IKahPbDvTp6rBLVCumHx3UKKQUEstQ9steGW', true, NULL, '2026-06-09 00:25:33+00');
INSERT INTO public.audit_logs VALUES ('a1fa61d4-4eba-4377-9d90-bbef9ba945fe', 'e1a77ba8-73cb-4a73-979b-fb532d0b99e5', 'responsable@ucad.edu.sn', 'axe_admin', 'login', 'user', 'e1a77ba8-73cb-4a73-979b-fb532d0b99e5', '{"mode": "keycloak_direct", "role": "axe_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'MhiWDRBVD4uHt2POp4HKsqDnHRBL61TaRMJxzuN2', true, NULL, '2026-06-09 01:12:32+00');
INSERT INTO public.audit_logs VALUES ('a1fa6552-2823-4f60-8e32-bc0e8e942f22', 'e1a77ba8-73cb-4a73-979b-fb532d0b99e5', 'responsable@ucad.edu.sn', 'axe_admin', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'MhiWDRBVD4uHt2POp4HKsqDnHRBL61TaRMJxzuN2', true, NULL, '2026-06-09 01:22:17+00');
INSERT INTO public.audit_logs VALUES ('a1fa6619-3540-47be-a43b-0c9019dfc485', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'login', 'user', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', '{"mode": "keycloak_direct", "role": "super_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'XdC4r3je9Cj16xuBh3lhmHbK8o2lstuHWm7DGvcE', true, NULL, '2026-06-09 01:24:28+00');
INSERT INTO public.audit_logs VALUES ('a1fa6735-34d2-4aff-bb3e-e40681c07396', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'publish', 'publication', 'a1fa672e-0428-4cbb-bc2d-68b93f436a37', '{"mode": "direct", "titre": "AEREZQZS"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'XdC4r3je9Cj16xuBh3lhmHbK8o2lstuHWm7DGvcE', true, NULL, '2026-06-09 01:27:34+00');
INSERT INTO public.audit_logs VALUES ('a1fa69f1-6025-4297-9cfb-bf0349e918ad', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'XdC4r3je9Cj16xuBh3lhmHbK8o2lstuHWm7DGvcE', true, NULL, '2026-06-09 01:35:13+00');
INSERT INTO public.audit_logs VALUES ('a1fa6b87-add3-46f1-a589-a7fd6e7a8614', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'login', 'user', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', '{"mode": "keycloak_direct", "role": "super_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '7B1L3WR7CSMd0rp8apGznFvSXXHEarujeAvESXJO', true, NULL, '2026-06-09 01:39:39+00');
INSERT INTO public.audit_logs VALUES ('a1fa6d99-d505-45fa-97cf-120f5580283c', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '7B1L3WR7CSMd0rp8apGznFvSXXHEarujeAvESXJO', true, NULL, '2026-06-09 01:45:27+00');
INSERT INTO public.audit_logs VALUES ('a1fa6db4-877a-4b3b-99ea-ad8c5bfc1e17', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', 'kurokotestusya@ucad.edu.sn', 'axe_admin', 'login', 'user', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', '{"mode": "keycloak_direct", "role": "axe_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'WySNX6vzxaHs96TuJRhabQnRciM9UEd0mDtkRQCs', true, NULL, '2026-06-09 01:45:44+00');
INSERT INTO public.audit_logs VALUES ('a1fa6df1-0706-4654-84b7-deb2b9c40fc8', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'delete', 'publication', 'a1fa4f02-e72a-449f-bd06-6a71c78b694a', '{"motif": "le contenu n''est pas lisible", "titre": "Rapport de recherche", "workflow": "majority_vote"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'WySNX6vzxaHs96TuJRhabQnRciM9UEd0mDtkRQCs', true, NULL, '2026-06-09 01:46:24+00');
INSERT INTO public.audit_logs VALUES ('a1fa6e50-9e01-433c-887d-0eb4ecdcb9c3', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', 'kurokotestusya@ucad.edu.sn', 'axe_admin', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'WySNX6vzxaHs96TuJRhabQnRciM9UEd0mDtkRQCs', true, NULL, '2026-06-09 01:47:26+00');
INSERT INTO public.audit_logs VALUES ('a1fa6e6f-b1d5-4dd0-a789-fea6bd70adab', 'a1fa6a70-3a9c-4eb0-b7fa-c3b6b38d9109', 'vulcan062004@gmail.com', 'doctoral_student', 'login', 'user', 'a1fa6a70-3a9c-4eb0-b7fa-c3b6b38d9109', '{"mode": "keycloak_direct", "role": "doctoral_student"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'hDaM0vXof6E0ysGpZTHAxO8DeXECZQwKOG8QWEfc', true, NULL, '2026-06-09 01:47:47+00');
INSERT INTO public.audit_logs VALUES ('a1fa6f14-4cef-4f63-8fea-6d4f16682910', 'a1fa6a70-3a9c-4eb0-b7fa-c3b6b38d9109', 'vulcan062004@gmail.com', 'doctoral_student', 'submit', 'publication', 'a1fa6f0d-ae7d-46e9-b4a9-7366271b57d2', '{"titre": "zerer", "axe_id": "ae57ef87-fc44-4049-a6e6-db90b867bac0"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'hDaM0vXof6E0ysGpZTHAxO8DeXECZQwKOG8QWEfc', true, NULL, '2026-06-09 01:49:34+00');
INSERT INTO public.audit_logs VALUES ('a1fa6f69-8a53-4280-b91e-521bc8d6b04e', 'a1fa6a70-3a9c-4eb0-b7fa-c3b6b38d9109', 'vulcan062004@gmail.com', 'doctoral_student', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'hDaM0vXof6E0ysGpZTHAxO8DeXECZQwKOG8QWEfc', true, NULL, '2026-06-09 01:50:31+00');
INSERT INTO public.audit_logs VALUES ('a1fa6f83-aef5-41d8-9abe-c99b92bab750', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', 'kurokotestusya@ucad.edu.sn', 'axe_admin', 'login', 'user', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', '{"mode": "keycloak_direct", "role": "axe_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'n0tGQiT5yqPSyH2I2mnDzu5KlddOPyMg2HhDpZ4o', true, NULL, '2026-06-09 01:50:48+00');
INSERT INTO public.audit_logs VALUES ('a1fa6ff4-b7e8-44b8-8111-c1f0846daeb7', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', 'kurokotestusya@ucad.edu.sn', 'axe_admin', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'n0tGQiT5yqPSyH2I2mnDzu5KlddOPyMg2HhDpZ4o', true, NULL, '2026-06-09 01:52:02+00');
INSERT INTO public.audit_logs VALUES ('a1fa7095-d70e-40e0-864e-7d5032564137', 'e1a77ba8-73cb-4a73-979b-fb532d0b99e5', 'responsable@ucad.edu.sn', 'axe_admin', 'login', 'user', 'e1a77ba8-73cb-4a73-979b-fb532d0b99e5', '{"mode": "keycloak_direct", "role": "axe_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'chmdw4xEg8eErIcyaLeMkAYzjssD61AynPfpTaDV', true, NULL, '2026-06-09 01:53:47+00');
INSERT INTO public.audit_logs VALUES ('a1fa70b5-5e2b-4877-bb76-168fc92e2aca', 'e1a77ba8-73cb-4a73-979b-fb532d0b99e5', 'responsable@ucad.edu.sn', 'axe_admin', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'chmdw4xEg8eErIcyaLeMkAYzjssD61AynPfpTaDV', true, NULL, '2026-06-09 01:54:08+00');
INSERT INTO public.audit_logs VALUES ('a1fa70cb-6bab-476d-87ec-4b34d2baa664', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'login', 'user', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', '{"mode": "keycloak_direct", "role": "super_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '7EW0o7KB5cBCCVaIWGZCuautKFbhx6KLR6VgQ6KH', true, NULL, '2026-06-09 01:54:22+00');
INSERT INTO public.audit_logs VALUES ('a1fa7160-1984-4f25-bd59-4eada56b4f1a', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'directeur@ucad.edu.sn', 'super_admin', 'logout', NULL, NULL, '[]', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '7EW0o7KB5cBCCVaIWGZCuautKFbhx6KLR6VgQ6KH', true, NULL, '2026-06-09 01:56:00+00');
INSERT INTO public.audit_logs VALUES ('a1fa7181-a834-434d-9e08-37790364a4e2', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', 'kurokotestusya@ucad.edu.sn', 'axe_admin', 'login', 'user', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', '{"mode": "keycloak_direct", "role": "axe_admin"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'UF0AP8jQHeXQUYr6QL5TFjDHEylhHNYllfW1QUgu', true, NULL, '2026-06-09 01:56:22+00');
INSERT INTO public.audit_logs VALUES ('e19a4d78-7a33-4d84-82c0-b1613d9eb149', 'a1fa6a70-3a9c-4eb0-b7fa-c3b6b38d9109', 'vulcan062004@gmail.com', NULL, 'publish', 'publication', 'a1fa6f0d-ae7d-46e9-b4a9-7366271b57d2', '{"titre": "zerer", "statut_apres": "published", "statut_avant": "submitted"}', NULL, NULL, NULL, true, NULL, '2026-06-09 02:00:21.863059+00');
INSERT INTO public.audit_logs VALUES ('a1fa72f0-e04e-4a6f-9a54-9961f77f415e', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', 'kurokotestusya@ucad.edu.sn', 'axe_admin', 'approve', 'publication', 'a1fa6f0d-ae7d-46e9-b4a9-7366271b57d2', '{"titre": "zerer", "workflow_id": "a1fa6f14-31ae-4bc3-9003-2dcf8c0b6f98"}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'UF0AP8jQHeXQUYr6QL5TFjDHEylhHNYllfW1QUgu', true, NULL, '2026-06-09 02:00:23+00');


--
-- Data for Name: axes_thematiques; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.axes_thematiques VALUES ('03ec32a7-8889-4b39-9024-55c3d56ba598', 'fablab', 'FabLab & Makers', 'FabLab & Makers', 'Fabrication numérique, impression 3D, électronique, prototypage et innovation ouverte.', NULL, NULL, '#993C1D', 3, true, '2026-06-06 21:53:49.177933+00', '2026-06-06 21:53:49.177933+00', NULL);
INSERT INTO public.axes_thematiques VALUES ('2e8037a2-fc6f-4509-81db-3bac25160a6a', 'iot', 'IoT & Capteurs', 'IoT & Sensors', 'Objets connectés, réseaux de capteurs, acquisition de données terrain, applications embarquées.', NULL, NULL, '#854F0B', 4, true, '2026-06-06 21:53:49.177933+00', '2026-06-06 21:53:49.177933+00', NULL);
INSERT INTO public.axes_thematiques VALUES ('39a77ba8-73cb-4a73-979b-fb532d0b99e5', 'epidemio', 'Épidémiologie Numérique', 'Digital Epidemiology', 'Modélisation mathématique et informatique des maladies infectieuses, dynamiques épidémiques et systèmes de surveillance.', NULL, NULL, '#1F4E79', 1, true, '2026-06-06 21:53:49.177933+00', '2026-06-08 11:12:00.885535+00', 'e1a77ba8-73cb-4a73-979b-fb532d0b99e5');
INSERT INTO public.axes_thematiques VALUES ('5f7e7356-761f-48e7-bddc-3dd8d19bd631', 'climat', 'Modélisation Climatique', 'Climate Modelling', 'Simulation des systèmes climatiques, impact du changement climatique, modèles de prévision environnementale.', NULL, NULL, '#0F6E5 ', 2, true, '2026-06-06 21:53:49.177933+00', '2026-06-09 01:33:27.827967+00', NULL);
INSERT INTO public.axes_thematiques VALUES ('ae57ef87-fc44-4049-a6e6-db90b867bac0', 'methodes', 'Méthodes & Algorithmes', 'Methods & Algorithms', 'Développement de méthodes mathématiques et algorithmiques pour la modélisation de systèmes complexes.', NULL, NULL, '#534AB7', 5, true, '2026-06-06 21:53:49.177933+00', '2026-06-09 01:44:09.345014+00', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7');


--
-- Data for Name: chatbot_feedbacks; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: chatbot_sessions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: controle_acces; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: conventions_stage; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: datasets; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: datasets_fichiers; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: datasets_versions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: demandes_collaboration; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: demandes_contact; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.demandes_contact VALUES ('dc290577-db38-4c4b-a560-b899b7457069', 'Test User', 'test@example.com', 'Test Org', 'Test subject for UMMISCO', 'Hello, this is a test message to verify the contact form works correctly.', 'contact', NULL, false, NULL, NULL, NULL, '172.18.0.1', '2026-06-08 10:06:55+00');


--
-- Data for Name: demandes_suppression; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.demandes_suppression VALUES ('1e265fbf-eeea-443b-bbad-ab66bb06d593', 'a1fa4f02-e72a-449f-bd06-6a71c78b694a', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'le contenu n''est pas lisible', 'approved', '2026-06-09 01:34:48+00', '2026-06-09 01:46:23+00');


--
-- Data for Name: documents; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.documents VALUES ('a1fa4f02-e72a-449f-bd06-6a71c78b694a', 'publications/a1fa4f02-e72a-449f-bd06-6a71c78b694a/cours_va_processus.pdf', 'cours_va_processus.pdf', 355263, 'application/pdf', NULL, false, NULL, NULL);
INSERT INTO public.documents VALUES ('a1fa672e-0428-4cbb-bc2d-68b93f436a37', 'documents/31OMlJ1DFGpmtHsCOngKOF4wL5BucCl6OiDHBhPF.pdf', 'Cours-1 Interconnexion des réseaux.pdf', 578960, 'application/pdf', NULL, false, NULL, NULL);
INSERT INTO public.documents VALUES ('a1fa6f0d-ae7d-46e9-b4a9-7366271b57d2', 'documents/ToTfoBEZTo9U5kTR25PLGq4xaaMmyr8tMsyM5eJf.pdf', 'Chapitre 0 Généralités et Historique.pdf', 442995, 'application/pdf', NULL, false, NULL, NULL);


--
-- Data for Name: evenements; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: medias; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: newsletter_abonnes; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: notifications_templates; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.notifications_templates VALUES ('0f364ed4-28dd-48c3-b133-eda9a12b121b', 'workflow_submitted_fr', 'fr', 'workflow_submitted', '[UMMISCO] Nouvelle soumission à valider : {{titre}}', 'Bonjour {{admin_prenom}},

Une nouvelle soumission est en attente de validation dans votre axe "{{axe_nom}}".

Titre : {{titre}}
Soumis par : {{auteur_prenom}} {{auteur_nom}}
Date de soumission : {{date_soumission}}

Connectez-vous au back-office pour examiner cette soumission :
{{lien_validation}}

Cordialement,
Le portail UMMISCO', NULL, true, '2026-06-06 21:53:49.182524+00', '2026-06-06 21:53:49.182524+00');
INSERT INTO public.notifications_templates VALUES ('9d1b5a9a-3cae-4bb2-9a64-475821f74aba', 'workflow_approved_fr', 'fr', 'workflow_approved', '[UMMISCO] Votre contenu a été publié : {{titre}}', 'Bonjour {{auteur_prenom}},

Votre contenu "{{titre}}" a été validé et publié sur le portail UMMISCO.

Vous pouvez le consulter à l''adresse suivante :
{{lien_publication}}

Cordialement,
L''équipe UMMISCO', NULL, true, '2026-06-06 21:53:49.182524+00', '2026-06-06 21:53:49.182524+00');
INSERT INTO public.notifications_templates VALUES ('36a5c73e-ff27-4d81-adef-56179a9d57e9', 'workflow_rejected_fr', 'fr', 'workflow_rejected', '[UMMISCO] Révision requise pour votre soumission : {{titre}}', 'Bonjour {{auteur_prenom}},

Votre soumission "{{titre}}" nécessite des révisions avant publication.

Commentaire du validateur :
{{commentaire_admin}}

Connectez-vous pour apporter les corrections demandées :
{{lien_soumission}}

Cordialement,
L''équipe UMMISCO', NULL, true, '2026-06-06 21:53:49.182524+00', '2026-06-06 21:53:49.182524+00');
INSERT INTO public.notifications_templates VALUES ('6a20af1a-8c5b-4064-8d86-65f1955bf7e4', 'newsletter_welcome_fr', 'fr', 'newsletter_welcome', 'Bienvenue dans la newsletter UMMISCO !', 'Bonjour {{nom}},

Merci de vous être abonné à la newsletter du laboratoire UMMISCO.

Vous recevrez désormais nos actualités, publications récentes et événements scientifiques.

Pour vous désabonner à tout moment : {{lien_desabonnement}}

L''équipe UMMISCO', NULL, true, '2026-06-06 21:53:49.182524+00', '2026-06-06 21:53:49.182524+00');


--
-- Data for Name: outils_doctoraux; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: parametres_systeme; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.parametres_systeme VALUES ('site_nom_fr', 'Portail UMMISCO', 'Nom du portail en français', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('site_nom_en', 'UMMISCO Portal', 'Nom du portail en anglais', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('site_description_fr', 'Portail web du laboratoire UMMISCO', 'Description SEO française', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('site_description_en', 'UMMISCO Laboratory Web Portal', 'Description SEO anglaise', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('site_url', 'https://ummisco.ucad.sn', 'URL publique du portail', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('admin_email', 'admin@ummisco.ucad.sn', 'Email administrateur principal', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('langue_defaut', 'fr', 'Langue par défaut du portail', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('nb_publications_page', '12', 'Nombre de publications par page', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('max_upload_mo', '50', 'Taille max upload en Mo', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('formats_autorises', 'pdf,docx,csv,xlsx,json,zip,netcdf,hdf5', 'Extensions de fichier autorisées', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('chatbot_actif', 'true', 'Activer le chatbot IA', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('chatbot_modele', 'llama3', 'Modèle Ollama utilisé', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('chatbot_max_tokens', '2048', 'Tokens max par réponse chatbot', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('maintenance_mode', 'false', 'Mode maintenance du portail', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('newsletter_actif', 'true', 'Activer la newsletter', true, '2026-06-06 21:53:49.174064+00');
INSERT INTO public.parametres_systeme VALUES ('workflow_delai_jours', '14', 'Délai de validation en jours', true, '2026-06-06 21:53:49.174064+00');


--
-- Data for Name: profils_chercheurs; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.profils_chercheurs VALUES ('a1fa27b0-32b2-4606-8932-626ba3e94f55', 'Data Science', NULL, NULL, 1, NULL, NULL, '2026-06-09 00:19:54.023317+00');
INSERT INTO public.profils_chercheurs VALUES ('a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', 'Méthodes & Algorithmes', NULL, NULL, 0, NULL, NULL, '2026-06-09 01:39:05+00');


--
-- Data for Name: profils_doctorants; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.profils_doctorants VALUES ('a1fa6a70-3a9c-4eb0-b7fa-c3b6b38d9109', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09 01:36:36+00', 'Réseaux neurones');


--
-- Data for Name: profils_partenaires; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: publications; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.publications VALUES ('a1f7add5-a3c2-4adc-86e6-5d8ffc2cdca0', 'Document de Recherche UMMISCO', NULL, 'RResume de test', NULL, 'report', 'published', 'public', 'fr', 'a1f63bf2-cd1c-4013-8da1-8873065e92a4', '39a77ba8-73cb-4a73-979b-fb532d0b99e5', NULL, NULL, 0, 0, '2026-06-07 16:57:31+00', '2026-06-07 16:57:31+00', '2026-06-07 16:57:35+00', '2026-06-07 16:57:35+00', NULL, DEFAULT, DEFAULT);
INSERT INTO public.publications VALUES ('a1f7a1b3-1c51-4923-981e-af900aec2d51', 'Modelisation mathematique du paludisme au Senegal', NULL, 'Un modele mathematique deterministe pour analyser la propagation du paludisme.', NULL, 'article', 'published', 'public', 'fr', 'a1f79b56-5960-49e4-a23e-f68e83df330d', '39a77ba8-73cb-4a73-979b-fb532d0b99e5', NULL, NULL, 0, 0, '2026-06-08 14:03:32+00', '2026-06-07 16:23:35+00', '2026-06-07 16:23:39+00', '2026-06-08 14:03:32.002507+00', NULL, DEFAULT, DEFAULT);
INSERT INTO public.publications VALUES ('a1fa672e-0428-4cbb-bc2d-68b93f436a37', 'AEREZQZS', NULL, 'AZEDFGDXZA', NULL, 'article', 'published', 'internal', 'fr', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', '39a77ba8-73cb-4a73-979b-fb532d0b99e5', '{AGEZ}', NULL, 0, 0, '2026-06-09 01:27:28+00', '2026-06-09 01:27:28+00', '2026-06-09 01:27:29+00', '2026-06-09 01:27:29+00', NULL, DEFAULT, DEFAULT);
INSERT INTO public.publications VALUES ('a1fa4f02-e72a-449f-bd06-6a71c78b694a', 'Rapport de recherche', NULL, 'Rapport de recherche', NULL, 'report', 'published', 'public', 'fr', 'a1fa27b0-32b2-4606-8932-626ba3e94f55', 'ae57ef87-fc44-4049-a6e6-db90b867bac0', '{Algo}', NULL, 0, 0, '2026-06-09 00:19:54+00', '2026-06-09 00:19:54+00', '2026-06-09 00:19:55+00', '2026-06-09 01:46:19.066749+00', '2026-06-09 01:46:23+00', DEFAULT, DEFAULT);
INSERT INTO public.publications VALUES ('a1fa6f0d-ae7d-46e9-b4a9-7366271b57d2', 'zerer', NULL, 'azerfgf', NULL, 'report', 'published', 'public', 'fr', 'a1fa6a70-3a9c-4eb0-b7fa-c3b6b38d9109', 'ae57ef87-fc44-4049-a6e6-db90b867bac0', '{}', NULL, 0, 0, '2026-06-09 02:00:22+00', '2026-06-09 01:49:29+00', '2026-06-09 01:49:30+00', '2026-06-09 02:00:21.863059+00', NULL, DEFAULT, DEFAULT);


--
-- Data for Name: publications_auteurs; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: publications_medias; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: publications_tags; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: tags; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.tags VALUES ('27201104-3ded-4124-8dee-43b36f347af9', 'Modélisation', 'Modelling', 'modelisation');
INSERT INTO public.tags VALUES ('bcf52f2a-a69a-4928-81be-2d0f46fe270b', 'Simulation', 'Simulation', 'simulation');
INSERT INTO public.tags VALUES ('f0e9bce3-0a9f-4593-bb32-14623a3c29e2', 'Épidémiologie', 'Epidemiology', 'epidemiologie');
INSERT INTO public.tags VALUES ('b7aa9923-1bb7-4c10-a69d-9bd4242bf0be', 'Machine Learning', 'Machine Learning', 'machine-learning');
INSERT INTO public.tags VALUES ('f0f971d5-2fcc-4d90-9ca5-e6363d304d21', 'Données ouvertes', 'Open Data', 'donnees-ouvertes');
INSERT INTO public.tags VALUES ('12406ae4-5f56-4467-8dcd-39a484942a85', 'Afrique', 'Africa', 'afrique');
INSERT INTO public.tags VALUES ('7da491d3-9d90-4944-97e6-658e86b42a8d', 'Sénégal', 'Senegal', 'senegal');
INSERT INTO public.tags VALUES ('e7ae2ccd-4731-4e4d-af89-d68cf172f063', 'Santé publique', 'Public Health', 'sante-publique');
INSERT INTO public.tags VALUES ('ea2262ae-498e-4381-b515-15ce6480e69d', 'Changement climatique', 'Climate Change', 'changement-climatique');
INSERT INTO public.tags VALUES ('fcf2d345-ece0-4aff-8907-a444637f0956', 'IoT', 'IoT', 'iot');
INSERT INTO public.tags VALUES ('30e23c32-c80e-4628-a1ab-6ef479041fee', 'Mathématiques', 'Mathematics', 'mathematiques');
INSERT INTO public.tags VALUES ('9d462637-9032-4d0a-9fd0-f65f0e25bf40', 'Intelligence artificielle', 'Artificial Intelligence', 'intelligence-artificielle');
INSERT INTO public.tags VALUES ('89c8956a-8f56-4029-9335-e2dea01360ee', 'Biodiversité', 'Biodiversity', 'biodiversite');
INSERT INTO public.tags VALUES ('60e46bc8-84c7-48a4-a9af-6426b59f8064', 'Eau', 'Water', 'eau');
INSERT INTO public.tags VALUES ('9081382b-d449-42ee-b83d-0ca5e1375389', 'Agriculture', 'Agriculture', 'agriculture');


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.users VALUES ('e0b9eb52-0f70-49dc-92ab-21a2f334b433', '00000000-0000-0000-0000-000000000001', 'admin@ummisco.ucad.sn', 'Admin', 'Système', 'super_admin', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fr', true, NULL, '2026-06-06 21:53:49.191004+00', '2026-06-06 21:53:49.191004+00', NULL, NULL);
INSERT INTO public.users VALUES ('a1f79b56-5960-49e4-a23e-f68e83df330d', 'mock-doctoral_student-6a2596db1ba80', 'mock_doctoral_student@ummisco.ucad.sn', 'Doctoral student', 'Test', 'partner', 'archived', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fr', true, NULL, '2026-06-07 16:05:52+00', '2026-06-07 16:42:20.088148+00', NULL, NULL);
INSERT INTO public.users VALUES ('a1fa6a70-3a9c-4eb0-b7fa-c3b6b38d9109', '45886913-fd2a-4296-b7bd-8d03c65eb17a', 'vulcan062004@gmail.com', 'WADE', 'papa', 'doctoral_student', 'active', 'ae57ef87-fc44-4049-a6e6-db90b867bac0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fr', true, '2026-06-09 01:47:46+00', '2026-06-09 01:36:36+00', '2026-06-09 01:47:46.863134+00', NULL, NULL);
INSERT INTO public.users VALUES ('e1a77ba8-73cb-4a73-979b-fb532d0b99e5', 'responsable-local', 'responsable@ucad.edu.sn', 'Ndiaye', 'Moussa', 'axe_admin', 'active', '39a77ba8-73cb-4a73-979b-fb532d0b99e5', NULL, NULL, NULL, 'Dr', NULL, NULL, NULL, NULL, NULL, 'en', false, '2026-06-09 01:53:46+00', '2026-06-08 11:12:00.885535+00', '2026-06-09 01:53:46.998352+00', NULL, '$2y$10$I10PXrL8saeH3wRp93WJwOTHOIjjGRr7s..iAa6dDvQCIoRDmVtEq');
INSERT INTO public.users VALUES ('ac86624f-9adf-4eaa-a2b4-35e4ab25259e', '7d485d56-3358-45fe-a593-f779ad08bc59', 'directeur@ucad.edu.sn', 'UMMISCO', 'Directeur', 'super_admin', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'en', true, '2026-06-09 01:54:22+00', '2026-06-08 11:05:37.982267+00', '2026-06-09 01:54:22.3728+00', NULL, '$2y$10$I10PXrL8saeH3wRp93WJwOTHOIjjGRr7s..iAa6dDvQCIoRDmVtEq');
INSERT INTO public.users VALUES ('a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', '5851b266-933d-49dc-89b5-b15cb808207f', 'kurokotestusya@ucad.edu.sn', 'kuroko', 'tetsuya', 'axe_admin', 'active', 'ae57ef87-fc44-4049-a6e6-db90b867bac0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fr', true, '2026-06-09 01:56:21+00', '2026-06-09 01:39:05+00', '2026-06-09 01:56:21.463215+00', NULL, NULL);
INSERT INTO public.users VALUES ('a1f63bf2-cd1c-4013-8da1-8873065e92a4', 'mock-researcher-6a24b09599682', 'mock_researcher@ummisco.ucad.sn', 'Researcher', 'Test', 'researcher', 'active', '39a77ba8-73cb-4a73-979b-fb532d0b99e5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fr', true, NULL, '2026-06-06 23:43:18+00', '2026-06-08 23:22:22.611276+00', NULL, NULL);
INSERT INTO public.users VALUES ('a1fa27b0-32b2-4606-8932-626ba3e94f55', '1048fba3-38e8-42fe-a073-2d5dbee856fc', 'papawade183@gmail.com', 'WADE', 'Malick', 'researcher', 'active', '39a77ba8-73cb-4a73-979b-fb532d0b99e5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fr', true, '2026-06-09 00:15:18+00', '2026-06-08 22:29:57+00', '2026-06-09 00:15:18.726113+00', NULL, NULL);
INSERT INTO public.users VALUES ('a1f63b58-d148-4396-a293-83e8b69d271b', 'mock-super_admin-6a24b02f67a6e', 'mock_super_admin@ummisco.ucad.sn', 'Super admin', 'Test', 'super_admin', 'inactive', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fr', true, NULL, '2026-06-06 23:41:37+00', '2026-06-09 01:25:25.421775+00', NULL, NULL);


--
-- Data for Name: users_axes; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: votes_suppression; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.votes_suppression VALUES ('0416e4a5-bfe9-4880-bbc2-82d212db8487', '1e265fbf-eeea-443b-bbad-ab66bb06d593', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', true, '2026-06-09 01:46:19.066749+00', '2026-06-09 01:46:19+00');


--
-- Data for Name: workflow_historique; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.workflow_historique VALUES ('f7c7a6bf-20b2-4de9-adaa-5723869e5d9c', 'a1f7a1b4-c9dc-4daf-b09a-9b7f95bd8238', 'pending', 'approved', NULL, 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', '2026-06-08 14:03:32+00');
INSERT INTO public.workflow_historique VALUES ('b4586dd0-b38b-4b56-beae-bf5d49de9d23', 'a1fa6f14-31ae-4bc3-9003-2dcf8c0b6f98', 'pending', 'approved', NULL, 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', '2026-06-09 02:00:22+00');


--
-- Data for Name: workflow_validations; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.workflow_validations VALUES ('a1f7a1b4-c9dc-4daf-b09a-9b7f95bd8238', 'a1f7a1b3-1c51-4923-981e-af900aec2d51', 'a1f79b56-5960-49e4-a23e-f68e83df330d', 'ac86624f-9adf-4eaa-a2b4-35e4ab25259e', 'approved', NULL, NULL, 1, '2026-06-07 16:23:39+00', '2026-06-08 14:03:32+00', '2026-06-21 16:23:39+00', '2026-06-07 16:23:39+00', '2026-06-08 14:03:32.002507+00');
INSERT INTO public.workflow_validations VALUES ('a1fa6f14-31ae-4bc3-9003-2dcf8c0b6f98', 'a1fa6f0d-ae7d-46e9-b4a9-7366271b57d2', 'a1fa6a70-3a9c-4eb0-b7fa-c3b6b38d9109', 'a1fa6b52-f6d3-486f-8f71-e8b55d1845b7', 'approved', NULL, NULL, 1, '2026-06-09 01:49:34+00', '2026-06-09 02:00:21+00', '2026-06-23 01:49:34+00', '2026-06-09 01:49:34+00', '2026-06-09 02:00:21.863059+00');


--
-- Name: seq_convention_numero; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.seq_convention_numero', 1, false);


--
-- Name: actualites actualites_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actualites
    ADD CONSTRAINT actualites_pkey PRIMARY KEY (publication_id);


--
-- Name: articles articles_doi_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.articles
    ADD CONSTRAINT articles_doi_key UNIQUE (doi);


--
-- Name: articles articles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.articles
    ADD CONSTRAINT articles_pkey PRIMARY KEY (publication_id);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: axes_thematiques axes_thematiques_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.axes_thematiques
    ADD CONSTRAINT axes_thematiques_code_key UNIQUE (code);


--
-- Name: axes_thematiques axes_thematiques_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.axes_thematiques
    ADD CONSTRAINT axes_thematiques_pkey PRIMARY KEY (id);


--
-- Name: chatbot_feedbacks chatbot_feedbacks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chatbot_feedbacks
    ADD CONSTRAINT chatbot_feedbacks_pkey PRIMARY KEY (id);


--
-- Name: chatbot_sessions chatbot_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chatbot_sessions
    ADD CONSTRAINT chatbot_sessions_pkey PRIMARY KEY (id);


--
-- Name: chatbot_sessions chatbot_sessions_session_token_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chatbot_sessions
    ADD CONSTRAINT chatbot_sessions_session_token_key UNIQUE (session_token);


--
-- Name: controle_acces controle_acces_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.controle_acces
    ADD CONSTRAINT controle_acces_pkey PRIMARY KEY (id);


--
-- Name: controle_acces controle_acces_ressource_type_ressource_id_groupe_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.controle_acces
    ADD CONSTRAINT controle_acces_ressource_type_ressource_id_groupe_key UNIQUE (ressource_type, ressource_id, groupe);


--
-- Name: conventions_stage conventions_stage_numero_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conventions_stage
    ADD CONSTRAINT conventions_stage_numero_key UNIQUE (numero);


--
-- Name: conventions_stage conventions_stage_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conventions_stage
    ADD CONSTRAINT conventions_stage_pkey PRIMARY KEY (id);


--
-- Name: datasets datasets_doi_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datasets
    ADD CONSTRAINT datasets_doi_key UNIQUE (doi);


--
-- Name: datasets_fichiers datasets_fichiers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datasets_fichiers
    ADD CONSTRAINT datasets_fichiers_pkey PRIMARY KEY (id);


--
-- Name: datasets datasets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datasets
    ADD CONSTRAINT datasets_pkey PRIMARY KEY (publication_id);


--
-- Name: datasets_versions datasets_versions_dataset_id_version_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datasets_versions
    ADD CONSTRAINT datasets_versions_dataset_id_version_key UNIQUE (dataset_id, version);


--
-- Name: datasets_versions datasets_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datasets_versions
    ADD CONSTRAINT datasets_versions_pkey PRIMARY KEY (id);


--
-- Name: demandes_collaboration demandes_collaboration_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.demandes_collaboration
    ADD CONSTRAINT demandes_collaboration_pkey PRIMARY KEY (id);


--
-- Name: demandes_contact demandes_contact_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.demandes_contact
    ADD CONSTRAINT demandes_contact_pkey PRIMARY KEY (id);


--
-- Name: demandes_suppression demandes_suppression_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.demandes_suppression
    ADD CONSTRAINT demandes_suppression_pkey PRIMARY KEY (id);


--
-- Name: documents documents_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_pkey PRIMARY KEY (publication_id);


--
-- Name: evenements evenements_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evenements
    ADD CONSTRAINT evenements_pkey PRIMARY KEY (publication_id);


--
-- Name: medias medias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.medias
    ADD CONSTRAINT medias_pkey PRIMARY KEY (id);


--
-- Name: newsletter_abonnes newsletter_abonnes_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.newsletter_abonnes
    ADD CONSTRAINT newsletter_abonnes_email_key UNIQUE (email);


--
-- Name: newsletter_abonnes newsletter_abonnes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.newsletter_abonnes
    ADD CONSTRAINT newsletter_abonnes_pkey PRIMARY KEY (id);


--
-- Name: newsletter_abonnes newsletter_abonnes_token_unsub_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.newsletter_abonnes
    ADD CONSTRAINT newsletter_abonnes_token_unsub_key UNIQUE (token_unsub);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: notifications_templates notifications_templates_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications_templates
    ADD CONSTRAINT notifications_templates_code_key UNIQUE (code);


--
-- Name: notifications_templates notifications_templates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications_templates
    ADD CONSTRAINT notifications_templates_pkey PRIMARY KEY (id);


--
-- Name: outils_doctoraux outils_doctoraux_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.outils_doctoraux
    ADD CONSTRAINT outils_doctoraux_pkey PRIMARY KEY (id);


--
-- Name: parametres_systeme parametres_systeme_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parametres_systeme
    ADD CONSTRAINT parametres_systeme_pkey PRIMARY KEY (cle);


--
-- Name: profils_chercheurs profils_chercheurs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.profils_chercheurs
    ADD CONSTRAINT profils_chercheurs_pkey PRIMARY KEY (user_id);


--
-- Name: profils_doctorants profils_doctorants_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.profils_doctorants
    ADD CONSTRAINT profils_doctorants_pkey PRIMARY KEY (user_id);


--
-- Name: profils_partenaires profils_partenaires_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.profils_partenaires
    ADD CONSTRAINT profils_partenaires_pkey PRIMARY KEY (user_id);


--
-- Name: publications_auteurs publications_auteurs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications_auteurs
    ADD CONSTRAINT publications_auteurs_pkey PRIMARY KEY (publication_id, user_id);


--
-- Name: publications_medias publications_medias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications_medias
    ADD CONSTRAINT publications_medias_pkey PRIMARY KEY (publication_id, media_id);


--
-- Name: publications publications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications
    ADD CONSTRAINT publications_pkey PRIMARY KEY (id);


--
-- Name: publications_tags publications_tags_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications_tags
    ADD CONSTRAINT publications_tags_pkey PRIMARY KEY (publication_id, tag_id);


--
-- Name: tags tags_nom_fr_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_nom_fr_key UNIQUE (nom_fr);


--
-- Name: tags tags_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id);


--
-- Name: tags tags_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_slug_key UNIQUE (slug);


--
-- Name: users_axes users_axes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users_axes
    ADD CONSTRAINT users_axes_pkey PRIMARY KEY (user_id, axe_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_keycloak_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_keycloak_id_key UNIQUE (keycloak_id);


--
-- Name: users users_orcid_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_orcid_id_key UNIQUE (orcid_id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: votes_suppression votes_suppression_demande_suppression_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.votes_suppression
    ADD CONSTRAINT votes_suppression_demande_suppression_id_user_id_key UNIQUE (demande_suppression_id, user_id);


--
-- Name: votes_suppression votes_suppression_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.votes_suppression
    ADD CONSTRAINT votes_suppression_pkey PRIMARY KEY (id);


--
-- Name: workflow_historique workflow_historique_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_historique
    ADD CONSTRAINT workflow_historique_pkey PRIMARY KEY (id);


--
-- Name: workflow_validations workflow_validations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_validations
    ADD CONSTRAINT workflow_validations_pkey PRIMARY KEY (id);


--
-- Name: idx_acl_groupe; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_acl_groupe ON public.controle_acces USING btree (groupe);


--
-- Name: idx_acl_ressource; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_acl_ressource ON public.controle_acces USING btree (ressource_type, ressource_id);


--
-- Name: idx_articles_annee; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_articles_annee ON public.articles USING btree (annee_publication);


--
-- Name: idx_articles_doi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_articles_doi ON public.articles USING btree (doi) WHERE (doi IS NOT NULL);


--
-- Name: idx_audit_action; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_action ON public.audit_logs USING btree (action);


--
-- Name: idx_audit_created_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_created_at ON public.audit_logs USING btree (created_at DESC);


--
-- Name: idx_audit_ressource; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_ressource ON public.audit_logs USING btree (ressource_type, ressource_id);


--
-- Name: idx_audit_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_user ON public.audit_logs USING btree (user_id);


--
-- Name: idx_datasets_licence; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_datasets_licence ON public.datasets USING btree (licence);


--
-- Name: idx_datasets_metadonnees; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_datasets_metadonnees ON public.datasets USING gin (metadonnees);


--
-- Name: idx_evenements_dates; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_evenements_dates ON public.evenements USING btree (date_debut, date_fin);


--
-- Name: idx_notif_destinataire; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_notif_destinataire ON public.notifications USING btree (destinataire_id);


--
-- Name: idx_notif_statut; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_notif_statut ON public.notifications USING btree (statut) WHERE (statut = 'pending'::public.notification_status);


--
-- Name: idx_outils_axe; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_outils_axe ON public.outils_doctoraux USING btree (axe_id) WHERE (actif = true);


--
-- Name: idx_outils_doctorant; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_outils_doctorant ON public.outils_doctoraux USING btree (doctorant_id);


--
-- Name: idx_publications_auteur; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_publications_auteur ON public.publications USING btree (auteur_id);


--
-- Name: idx_publications_axe; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_publications_axe ON public.publications USING btree (axe_id);


--
-- Name: idx_publications_date_pub; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_publications_date_pub ON public.publications USING btree (date_publication DESC);


--
-- Name: idx_publications_deleted; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_publications_deleted ON public.publications USING btree (deleted_at) WHERE (deleted_at IS NULL);


--
-- Name: idx_publications_fts_en; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_publications_fts_en ON public.publications USING gin (fts_en);


--
-- Name: idx_publications_fts_fr; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_publications_fts_fr ON public.publications USING gin (fts_fr);


--
-- Name: idx_publications_mots_cles; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_publications_mots_cles ON public.publications USING gin (mots_cles);


--
-- Name: idx_publications_statut; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_publications_statut ON public.publications USING btree (statut);


--
-- Name: idx_publications_titre_trgm; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_publications_titre_trgm ON public.publications USING gin (titre_fr public.gin_trgm_ops);


--
-- Name: idx_publications_type; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_publications_type ON public.publications USING btree (type);


--
-- Name: idx_publications_visibilite; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_publications_visibilite ON public.publications USING btree (visibilite);


--
-- Name: idx_users_axe; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_axe ON public.users USING btree (axe_principal_id);


--
-- Name: idx_users_axes_axe; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_axes_axe ON public.users_axes USING btree (axe_id);


--
-- Name: idx_users_email; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_email ON public.users USING btree (email);


--
-- Name: idx_users_keycloak_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_keycloak_id ON public.users USING btree (keycloak_id);


--
-- Name: idx_users_nom_trgm; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_nom_trgm ON public.users USING gin (nom public.gin_trgm_ops);


--
-- Name: idx_users_prenom_trgm; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_prenom_trgm ON public.users USING gin (prenom public.gin_trgm_ops);


--
-- Name: idx_users_role; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_role ON public.users USING btree (role);


--
-- Name: idx_users_statut; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_statut ON public.users USING btree (statut) WHERE (statut = 'active'::public.user_status);


--
-- Name: idx_workflow_publication; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_workflow_publication ON public.workflow_validations USING btree (publication_id);


--
-- Name: idx_workflow_soumetteur; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_workflow_soumetteur ON public.workflow_validations USING btree (soumetteur_id);


--
-- Name: idx_workflow_statut; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_workflow_statut ON public.workflow_validations USING btree (statut);


--
-- Name: idx_workflow_validateur; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_workflow_validateur ON public.workflow_validations USING btree (validateur_id);


--
-- Name: audit_logs trg_audit_logs_no_delete; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_audit_logs_no_delete BEFORE DELETE ON public.audit_logs FOR EACH ROW EXECUTE FUNCTION public.fn_audit_logs_immutable();


--
-- Name: audit_logs trg_audit_logs_no_update; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_audit_logs_no_update BEFORE UPDATE ON public.audit_logs FOR EACH ROW EXECUTE FUNCTION public.fn_audit_logs_immutable();


--
-- Name: publications trg_audit_publication_statut; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_audit_publication_statut AFTER UPDATE OF statut ON public.publications FOR EACH ROW EXECUTE FUNCTION public.fn_audit_publication_statut();


--
-- Name: axes_thematiques trg_axes_thematiques_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_axes_thematiques_updated_at BEFORE UPDATE ON public.axes_thematiques FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: controle_acces trg_controle_acces_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_controle_acces_updated_at BEFORE UPDATE ON public.controle_acces FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: conventions_stage trg_convention_numero; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_convention_numero BEFORE INSERT ON public.conventions_stage FOR EACH ROW EXECUTE FUNCTION public.fn_generate_convention_numero();


--
-- Name: conventions_stage trg_conventions_stage_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_conventions_stage_updated_at BEFORE UPDATE ON public.conventions_stage FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: demandes_collaboration trg_demandes_collaboration_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_demandes_collaboration_updated_at BEFORE UPDATE ON public.demandes_collaboration FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: notifications_templates trg_notifications_templates_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_notifications_templates_updated_at BEFORE UPDATE ON public.notifications_templates FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: outils_doctoraux trg_outils_doctoraux_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_outils_doctoraux_updated_at BEFORE UPDATE ON public.outils_doctoraux FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: parametres_systeme trg_parametres_systeme_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_parametres_systeme_updated_at BEFORE UPDATE ON public.parametres_systeme FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: profils_chercheurs trg_profils_chercheurs_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_profils_chercheurs_updated_at BEFORE UPDATE ON public.profils_chercheurs FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: profils_doctorants trg_profils_doctorants_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_profils_doctorants_updated_at BEFORE UPDATE ON public.profils_doctorants FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: profils_partenaires trg_profils_partenaires_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_profils_partenaires_updated_at BEFORE UPDATE ON public.profils_partenaires FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: publications trg_publications_nb_count; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_publications_nb_count AFTER INSERT OR DELETE OR UPDATE OF statut ON public.publications FOR EACH ROW EXECUTE FUNCTION public.fn_update_nb_publications();


--
-- Name: publications trg_publications_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_publications_updated_at BEFORE UPDATE ON public.publications FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: users trg_users_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_users_updated_at BEFORE UPDATE ON public.users FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: workflow_validations trg_workflow_validations_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_workflow_validations_updated_at BEFORE UPDATE ON public.workflow_validations FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();


--
-- Name: actualites actualites_publication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actualites
    ADD CONSTRAINT actualites_publication_id_fkey FOREIGN KEY (publication_id) REFERENCES public.publications(id) ON DELETE CASCADE;


--
-- Name: articles articles_publication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.articles
    ADD CONSTRAINT articles_publication_id_fkey FOREIGN KEY (publication_id) REFERENCES public.publications(id) ON DELETE CASCADE;


--
-- Name: audit_logs audit_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: axes_thematiques axes_thematiques_responsable_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.axes_thematiques
    ADD CONSTRAINT axes_thematiques_responsable_id_fkey FOREIGN KEY (responsable_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: chatbot_feedbacks chatbot_feedbacks_session_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chatbot_feedbacks
    ADD CONSTRAINT chatbot_feedbacks_session_id_fkey FOREIGN KEY (session_id) REFERENCES public.chatbot_sessions(id) ON DELETE CASCADE;


--
-- Name: chatbot_sessions chatbot_sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chatbot_sessions
    ADD CONSTRAINT chatbot_sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: controle_acces controle_acces_accordé_par_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.controle_acces
    ADD CONSTRAINT "controle_acces_accordé_par_fkey" FOREIGN KEY ("accordé_par") REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: conventions_stage conventions_stage_axe_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conventions_stage
    ADD CONSTRAINT conventions_stage_axe_id_fkey FOREIGN KEY (axe_id) REFERENCES public.axes_thematiques(id) ON DELETE SET NULL;


--
-- Name: conventions_stage conventions_stage_encadrant_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conventions_stage
    ADD CONSTRAINT conventions_stage_encadrant_id_fkey FOREIGN KEY (encadrant_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: conventions_stage conventions_stage_stagiaire_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conventions_stage
    ADD CONSTRAINT conventions_stage_stagiaire_id_fkey FOREIGN KEY (stagiaire_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: conventions_stage conventions_stage_validee_par_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conventions_stage
    ADD CONSTRAINT conventions_stage_validee_par_fkey FOREIGN KEY (validee_par) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: datasets_fichiers datasets_fichiers_dataset_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datasets_fichiers
    ADD CONSTRAINT datasets_fichiers_dataset_id_fkey FOREIGN KEY (dataset_id) REFERENCES public.datasets(publication_id) ON DELETE CASCADE;


--
-- Name: datasets datasets_publication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datasets
    ADD CONSTRAINT datasets_publication_id_fkey FOREIGN KEY (publication_id) REFERENCES public.publications(id) ON DELETE CASCADE;


--
-- Name: datasets_versions datasets_versions_cree_par_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datasets_versions
    ADD CONSTRAINT datasets_versions_cree_par_fkey FOREIGN KEY (cree_par) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: datasets_versions datasets_versions_dataset_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datasets_versions
    ADD CONSTRAINT datasets_versions_dataset_id_fkey FOREIGN KEY (dataset_id) REFERENCES public.datasets(publication_id) ON DELETE CASCADE;


--
-- Name: demandes_collaboration demandes_collaboration_axe_cible_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.demandes_collaboration
    ADD CONSTRAINT demandes_collaboration_axe_cible_id_fkey FOREIGN KEY (axe_cible_id) REFERENCES public.axes_thematiques(id) ON DELETE SET NULL;


--
-- Name: demandes_collaboration demandes_collaboration_demandeur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.demandes_collaboration
    ADD CONSTRAINT demandes_collaboration_demandeur_id_fkey FOREIGN KEY (demandeur_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: demandes_collaboration demandes_collaboration_traite_par_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.demandes_collaboration
    ADD CONSTRAINT demandes_collaboration_traite_par_fkey FOREIGN KEY (traite_par) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: demandes_contact demandes_contact_axe_concerne_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.demandes_contact
    ADD CONSTRAINT demandes_contact_axe_concerne_id_fkey FOREIGN KEY (axe_concerne_id) REFERENCES public.axes_thematiques(id) ON DELETE SET NULL;


--
-- Name: demandes_contact demandes_contact_traite_par_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.demandes_contact
    ADD CONSTRAINT demandes_contact_traite_par_fkey FOREIGN KEY (traite_par) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: demandes_suppression demandes_suppression_propose_par_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.demandes_suppression
    ADD CONSTRAINT demandes_suppression_propose_par_fkey FOREIGN KEY (propose_par) REFERENCES public.users(id);


--
-- Name: demandes_suppression demandes_suppression_publication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.demandes_suppression
    ADD CONSTRAINT demandes_suppression_publication_id_fkey FOREIGN KEY (publication_id) REFERENCES public.publications(id) ON DELETE CASCADE;


--
-- Name: documents documents_publication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_publication_id_fkey FOREIGN KEY (publication_id) REFERENCES public.publications(id) ON DELETE CASCADE;


--
-- Name: evenements evenements_publication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evenements
    ADD CONSTRAINT evenements_publication_id_fkey FOREIGN KEY (publication_id) REFERENCES public.publications(id) ON DELETE CASCADE;


--
-- Name: medias medias_owner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.medias
    ADD CONSTRAINT medias_owner_id_fkey FOREIGN KEY (owner_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: notifications notifications_destinataire_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_destinataire_id_fkey FOREIGN KEY (destinataire_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: outils_doctoraux outils_doctoraux_axe_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.outils_doctoraux
    ADD CONSTRAINT outils_doctoraux_axe_id_fkey FOREIGN KEY (axe_id) REFERENCES public.axes_thematiques(id) ON DELETE SET NULL;


--
-- Name: outils_doctoraux outils_doctoraux_doctorant_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.outils_doctoraux
    ADD CONSTRAINT outils_doctoraux_doctorant_id_fkey FOREIGN KEY (doctorant_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: profils_chercheurs profils_chercheurs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.profils_chercheurs
    ADD CONSTRAINT profils_chercheurs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: profils_doctorants profils_doctorants_co_directeur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.profils_doctorants
    ADD CONSTRAINT profils_doctorants_co_directeur_id_fkey FOREIGN KEY (co_directeur_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: profils_doctorants profils_doctorants_directeur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.profils_doctorants
    ADD CONSTRAINT profils_doctorants_directeur_id_fkey FOREIGN KEY (directeur_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: profils_doctorants profils_doctorants_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.profils_doctorants
    ADD CONSTRAINT profils_doctorants_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: profils_partenaires profils_partenaires_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.profils_partenaires
    ADD CONSTRAINT profils_partenaires_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: publications publications_auteur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications
    ADD CONSTRAINT publications_auteur_id_fkey FOREIGN KEY (auteur_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: publications_auteurs publications_auteurs_publication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications_auteurs
    ADD CONSTRAINT publications_auteurs_publication_id_fkey FOREIGN KEY (publication_id) REFERENCES public.publications(id) ON DELETE CASCADE;


--
-- Name: publications_auteurs publications_auteurs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications_auteurs
    ADD CONSTRAINT publications_auteurs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: publications publications_axe_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications
    ADD CONSTRAINT publications_axe_id_fkey FOREIGN KEY (axe_id) REFERENCES public.axes_thematiques(id) ON DELETE SET NULL;


--
-- Name: publications_medias publications_medias_media_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications_medias
    ADD CONSTRAINT publications_medias_media_id_fkey FOREIGN KEY (media_id) REFERENCES public.medias(id) ON DELETE CASCADE;


--
-- Name: publications_medias publications_medias_publication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications_medias
    ADD CONSTRAINT publications_medias_publication_id_fkey FOREIGN KEY (publication_id) REFERENCES public.publications(id) ON DELETE CASCADE;


--
-- Name: publications_tags publications_tags_publication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications_tags
    ADD CONSTRAINT publications_tags_publication_id_fkey FOREIGN KEY (publication_id) REFERENCES public.publications(id) ON DELETE CASCADE;


--
-- Name: publications_tags publications_tags_tag_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.publications_tags
    ADD CONSTRAINT publications_tags_tag_id_fkey FOREIGN KEY (tag_id) REFERENCES public.tags(id) ON DELETE CASCADE;


--
-- Name: users users_axe_principal_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_axe_principal_id_fkey FOREIGN KEY (axe_principal_id) REFERENCES public.axes_thematiques(id) ON DELETE SET NULL;


--
-- Name: users_axes users_axes_axe_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users_axes
    ADD CONSTRAINT users_axes_axe_id_fkey FOREIGN KEY (axe_id) REFERENCES public.axes_thematiques(id) ON DELETE CASCADE;


--
-- Name: users_axes users_axes_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users_axes
    ADD CONSTRAINT users_axes_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: votes_suppression votes_suppression_demande_suppression_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.votes_suppression
    ADD CONSTRAINT votes_suppression_demande_suppression_id_fkey FOREIGN KEY (demande_suppression_id) REFERENCES public.demandes_suppression(id) ON DELETE CASCADE;


--
-- Name: votes_suppression votes_suppression_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.votes_suppression
    ADD CONSTRAINT votes_suppression_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: workflow_historique workflow_historique_acteur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_historique
    ADD CONSTRAINT workflow_historique_acteur_id_fkey FOREIGN KEY (acteur_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: workflow_historique workflow_historique_workflow_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_historique
    ADD CONSTRAINT workflow_historique_workflow_id_fkey FOREIGN KEY (workflow_id) REFERENCES public.workflow_validations(id) ON DELETE CASCADE;


--
-- Name: workflow_validations workflow_validations_publication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_validations
    ADD CONSTRAINT workflow_validations_publication_id_fkey FOREIGN KEY (publication_id) REFERENCES public.publications(id) ON DELETE CASCADE;


--
-- Name: workflow_validations workflow_validations_soumetteur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_validations
    ADD CONSTRAINT workflow_validations_soumetteur_id_fkey FOREIGN KEY (soumetteur_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: workflow_validations workflow_validations_validateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_validations
    ADD CONSTRAINT workflow_validations_validateur_id_fkey FOREIGN KEY (validateur_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

-- \unrestrict vdchEunZwPQTpKmVWmu3EGBWPXbMTo6NIi4Y7EliVTvmzLeaXtzJCJcOleTulGC

