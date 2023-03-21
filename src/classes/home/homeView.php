<?php
class HomeView extends View {
  private $title = 'Import home';
  private $content = '
  <a href="dataset?procedure=upload&dstoedit=dts">Upload file DST</a>
  <br><a href="dataset?procedure=upload&dstoedit=dts_test">Upload file ALTRO FILE</a>
  ';

  public function getHomePage(){
    $this->getPage($this->title, $this->content);
  }

}