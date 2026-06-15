<?php
$dir = __DIR__ . '/public/images';
$out = __DIR__ . '/resources/js/utils/logos.js';
if (!is_dir(dirname($out))) {
    mkdir(dirname($out), 0777, true);
}

function getBase64($file, $mime) {
    if (!file_exists($file)) return '';
    $data = file_get_contents($file);
    return 'data:' . $mime . ';base64,' . base64_encode($data);
}

$content = "export const logoIrd = '" . getBase64("$dir/logo_ird.jpg", 'image/jpeg') . "';\n";
$content .= "export const logoUcad = '" . getBase64("$dir/logo_ucad.webp", 'image/webp') . "';\n";
$content .= "export const logoUmmisco = '" . getBase64("$dir/logo_UMMISCO.webp", 'image/webp') . "';\n";
// Actually, let's also support the png version of ummisco
$content .= "export const logoReuUmmisco = '" . getBase64("$dir/logo_reu_ummisco.png", 'image/png') . "';\n";

file_put_contents($out, $content);
echo "logos.js generated\n";
