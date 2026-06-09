import zipfile
import xml.etree.ElementTree as ET

def extract_text_from_docx(docx_path):
    # docx files are zip archives containing XML files.
    # The main document body is in word/document.xml.
    try:
        with zipfile.ZipFile(docx_path) as z:
            doc_xml = z.read('word/document.xml')
            root = ET.fromstring(doc_xml)
            
            # Namespace for Word Processing ML
            namespaces = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}
            
            text_runs = []
            # Find all text elements
            for elem in root.findall('.//w:t', namespaces):
                if elem.text:
                    text_runs.append(elem.text)
            
            # Join text runs
            full_text = []
            current_paragraph = []
            
            # Grouping by paragraphs is better, but a simple run extraction might work.
            # Let's do a more robust paragraph-based extraction:
            for para in root.findall('.//w:p', namespaces):
                para_text = []
                for run in para.findall('.//w:t', namespaces):
                    if run.text:
                        para_text.append(run.text)
                if para_text:
                    full_text.append("".join(para_text))
            
            return "\n\n".join(full_text)
    except Exception as e:
        return f"Error reading docx: {e}"

docx_path = "UMMISCO_Dossier_Conception.docx"
text = extract_text_from_docx(docx_path)

with open("UMMISCO_Dossier_Conception.txt", "w", encoding="utf-8") as f:
    f.write(text)

print(f"Extracted document text, saved to UMMISCO_Dossier_Conception.txt, length: {len(text)} characters.")
