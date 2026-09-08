<?php
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}
echo "Carpeta uploads creada correctamente";
?>