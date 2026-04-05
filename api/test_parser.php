<?php
$python  = 'C:/Program Files/Python313/python.exe';
$script  = 'C:/xampp/htdocs/resume_shortlisting/python/rule_parser.py';
$pdfFile = 'C:/xampp/htdocs/resume_shortlisting/uploads/resumes/resume_51_1775192398.pdf';

$cmd    = "\"$python\" \"$script\" \"$pdfFile\" 2>&1";
$output = shell_exec($cmd);

echo "<pre>Command:\n$cmd\n\nOutput:\n" . htmlspecialchars($output) . "</pre>";
?>