<?php
$python = 'C:/Program Files/Python313/python.exe';

$v = shell_exec("\"$python\" --version 2>&1");
echo "Version: " . $v . "<br>";

$p = shell_exec("\"$python\" -c \"import pdfplumber; print('pdfplumber OK')\" 2>&1");
echo "pdfplumber: " . $p . "<br>";
?>