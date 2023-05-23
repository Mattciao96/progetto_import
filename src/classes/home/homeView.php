<?php
class HomeView extends View {
  private $title = 'Import home';
  private $content = '
  <a href="dataset?procedure=upload&dstoedit=dts">Upload DTS</a>
  <br><br><a href="dataset?procedure=upload&dstoedit=rilievi">Upload RILIEVI</a>
  <br><br><a href="dataset?procedure=upload&dstoedit=moderna">Upload MODERNA</a>
  <br><br><a href="dataset?procedure=upload&dstoedit=source">Upload SOURCE</a>
  <br><br><a href="dataset?procedure=upload&dstoedit=translation">Upload TRANSLATION</a>
  ';

  public function getHomePage(){
    $this->getPage($this->title, $this->content);
  }

}