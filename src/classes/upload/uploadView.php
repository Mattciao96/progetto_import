<?php
class UploadView extends View {

  private $title = 'Upload dataset';
  private $javascript_links = [
    'public/js/ext/sheetjs/xlsx.full.min.js',
    'public/js/import.js'
  ];


  private $content = '
  
  <p>
    Upload file, supported formats: csv, xls, xlsx, ods
  <p>
  Important:
  </p>
    <ul>
      <li>The first row MUST contain column names</li>
    </ul>
  </p>
    
    <div id = "download-area">
      <button id="download_csv">Download csv</button>
      <button id="download_xlsx">Download xlsx</button>
      <button id="download_ods">Download odt</button>
    </div>
    
    <form action="dataset?procedure=conceptmapping" method="post">
      <input type = "hidden" id="continue_procedure" name="dataset" />
      <button name="topo" id="topo" value="Continue">Continue</button>
    </form>
    
    <input type="file" id="input_dom_element">
    <div id = "preview-text"></div>
    <table id="tbl-data"></table>
  ';

  public function getUploadPage(){
    $ref = '<p>
    Database: '.$_SESSION['dstoedit'].'
  <p>';
    $this->getPage($this->title, $ref.$this->content, $this->javascript_links);
  }

}


