<?php
class MappingView extends View
{

  private $title = 'Mapping';
  private $javascript_links = ['public/js/mapping.js'];
  private $content_all;

  public function getOptionField($label, $options)
  {

    // create select form
    $content = '
    <label for="' . htmlspecialchars($label) . '">' . htmlspecialchars($label) . '</label>
      <select name="' . htmlspecialchars($label) . '" id="' . htmlspecialchars($label) . '-1" onchange="addValue(event)">
    ';



    // add all other options
    $labelInOptions = 0;
    $contentOption = '';
    foreach ($options as $option) {
      if (htmlspecialchars($option) == htmlspecialchars($label) and $labelInOptions == 0) {
        $contentOption .= '
        <option value="' . htmlspecialchars($option) . '" selected="selected">' . htmlspecialchars($option) . '</option>
      ';
        $labelInOptions = $labelInOptions + 1;
      } else {
        $contentOption .= '
        <option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</option>
      ';
      }
    }


    $contentOption .= '
      </select>
    ';

    // add no correspondence option

    if ($labelInOptions == 0) {
      $optionDefault = '
        <option value="" selected="selected">NO CORRISPONDENCE</option>
    ';
    } else {
      $optionDefault = '
      <option value="">NO CORRISPONDENCE</option>
  ';
    }

    $content = $content . $optionDefault . $contentOption;

    // add a button to add an additional select form


    return $content;
  }




  public function getAddButton($div_id)
  {
    $content = '<button type="button" id="add" onclick="addField(event)">Add</button>';
    return $content;
  }



  public function getManyOptions($labels, $options)
  {
    $content = '';
    $i = 1;
    foreach ($labels as $label) {
      $div_id = 'concept-' . $i;

      $content .= '<div id=' . $div_id . '>';
      $content .= $this->getOptionField($label, $options, $i);
      $content .= $this->getAddButton($div_id);
      $content .= '</div>';
      $content .= '<br>';
      $i++;
    }
    return $content;
  }

  public function setJavascriptVariable($encodedArray)
  {
    $content = '<script>let mappingValues = ' . $encodedArray . '</script>';
    $this->content_all .= $content;
  }

  public function getMappingContent($labels, $options)
  {
    $content = '<form action="dataset?procedure=conceptmapped" method="post">';
    $content .=  $this->getManyOptions($labels, $options);

    $content .= '
      <input type = "hidden" id="continue_procedure" name="dataset" />
      <button name="topo" id="topo" value="Continue" onclick="passConceptMapping()">Continue</button>';
    $this->content_all = $content;
  }

  public function getMappingPage()
  {
    $ref = "<p>Database: {$_SESSION['dstoedit']} </p>";
    $this->getPage($this->title, $ref.$this->content_all, $this->javascript_links);
  }
}
