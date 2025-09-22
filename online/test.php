<?php
libxml_use_internal_errors(true);
$dom = new Dom\HTMLDocument();
$catalog =
  "https://catalog.umkc.edu/colleges-schools/henry-w-bloch-management/graduate-programs/master-of-business-administration-business-analytics/";
$dom->loadHTMLFile($catalog);
$xpath = new DomXPath($dom);
$nodes = $xpath->query("//table/node()");

highlight_string(var_export($nodes, true));
foreach ($nodes as $i => $node) {
  echo "Node($i): ", $node->nodeValue, "\n";
}
libxml_use_internal_errors(false);
