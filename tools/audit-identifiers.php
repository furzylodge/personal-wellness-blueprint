<?php
declare(strict_types=1);

/**
 * Personal Wellness Blueprint
 * Identifier Audit Tool
 * Usage:
 *   php tools/audit-identifiers.php F
 *   php tools/audit-identifiers.php MEC
 *   php tools/audit-identifiers.php --all
 */

const EXCLUDED=['.git','vendor','node_modules','backup','cache','logs'];
const EXTENSIONS=['php','json','md','txt','html','htm','xml','yml','yaml','css','js'];
const IDENTIFIER_TYPES=[
'F'=>3,'FD'=>3,'BS'=>3,'MEC'=>3,'OS'=>3,'PP'=>3,'CA'=>3,'DF'=>3,'FA'=>3,'PS'=>3,'FC'=>3,'TP'=>3,'FB'=>6,'BM'=>6
];

function usage(): never {
    echo PHP_EOL;
    echo "Usage:".PHP_EOL;
    echo "  php tools/audit-identifiers.php PREFIX".PHP_EOL;
    echo "  php tools/audit-identifiers.php --all".PHP_EOL.PHP_EOL;
    echo "Supported: ".implode(', ',array_keys(IDENTIFIER_TYPES)).PHP_EOL;
    exit(1);
}

if($argc<2||$argv[1]==='--help'){usage();}

$patterns=[];
if(strtolower($argv[1])==='--all'){
    foreach(IDENTIFIER_TYPES as $p=>$d){
        $patterns[$p]='/\\b'.preg_quote($p,'/').'\\d{'.$d.'}\\b/';
    }
}else{
    $p=strtoupper(trim($argv[1]));
    if(!isset(IDENTIFIER_TYPES[$p])) usage();
    $patterns[$p]='/\\b'.preg_quote($p,'/').'\\d{'.IDENTIFIER_TYPES[$p].'}\\b/';
}

$root=realpath(__DIR__.'/..');
$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));

$totalFiles=$matchedFiles=$filenameMatches=$contentMatches=0;
$idTotals=[];

foreach($it as $file){
    if(!$file->isFile()) continue;
    $path=$file->getPathname();

    foreach(EXCLUDED as $ex){
        if(strpos($path,DIRECTORY_SEPARATOR.$ex.DIRECTORY_SEPARATOR)!==false) continue 2;
    }

    if(!in_array(strtolower($file->getExtension()),EXTENSIONS,true)) continue;

    $totalFiles++;
    $report=[];

    foreach($patterns as $pattern){

        if(preg_match_all($pattern,$file->getFilename(),$m)){
            if(!in_array('Filename',$report,true)) $report[]='Filename';
            foreach($m[0] as $id){
                $report[]='    '.$id;
                $filenameMatches++;
                $idTotals[$id]=($idTotals[$id]??0)+1;
            }
        }

        $lines=@file($path);
        if($lines===false) continue;

        foreach($lines as $ln=>$line){
            if(!preg_match_all($pattern,$line,$m)) continue;
            if(!in_array('Contents',$report,true)) $report[]='Contents';
            foreach($m[0] as $id){
                $report[]=sprintf('    Line %-5d %s',$ln+1,$id);
                $contentMatches++;
                $idTotals[$id]=($idTotals[$id]??0)+1;
            }
        }
    }

    if(empty($report)) continue;
    $matchedFiles++;
    echo str_repeat('-',62).PHP_EOL;
    echo str_replace($root.DIRECTORY_SEPARATOR,'',$path).PHP_EOL;
    foreach($report as $r) echo $r.PHP_EOL;
    echo PHP_EOL;
}

ksort($idTotals);

echo str_repeat('=',62).PHP_EOL;
echo "Identifier Totals".PHP_EOL;
echo str_repeat('=',62).PHP_EOL;
foreach($idTotals as $id=>$count){
    printf("%-10s %5d\n",$id,$count);
}
echo PHP_EOL.str_repeat('=',62).PHP_EOL;
echo "Summary".PHP_EOL;
echo str_repeat('=',62).PHP_EOL;
printf("Files scanned      : %d\n",$totalFiles);
printf("Files with matches : %d\n",$matchedFiles);
printf("Filename matches   : %d\n",$filenameMatches);
printf("Content matches    : %d\n",$contentMatches);
printf("Unique IDs         : %d\n",count($idTotals));
echo str_repeat('=',62).PHP_EOL;
