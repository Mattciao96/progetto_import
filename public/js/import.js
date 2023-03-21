const tableID = 'tbl-data';
const previewID = 'preview-text';
const supportedFormats = ['csv', 'xls', 'xlsx', 'txt', 'ods', 'xml','dbs','dbf'];
const buttonsIdArray = ['download_csv', 'download_xlsx', 'download_ods', 'topo']
let worksheet;
let workbook;
//let header;
//let wsj;



/**
 * get an array of dom elements from their id
 */
getElementsToInteract = (idArray) => {
  const domElArray = idArray.map(element =>{
    return document.getElementById(element);
  });
  return domElArray;
}
/**
 * deactivate an array of dom elements
 * state 'active' or 'disabled'
 */
setActivationState = (elementsArray, state) => {
  const changedElArray = elementsArray.map(element =>{
    switch (state) {
      case 'active':
        element.disabled = false;
        break;
      case 'disabled':
        element.disabled = true;
        break;
    }
    return element;
  });
  return changedElArray;
}

const getWorksheet = (data, opts) => {
  const workbookProvv = XLSX.read(data, opts);
  const worksheetProvv = workbookProvv.Sheets[workbookProvv.SheetNames[0]];
  return worksheetProvv;
}

const setWorksheet = (data, opts) => {
  workbook = XLSX.read(data, opts);
  worksheet = workbook.Sheets[workbook.SheetNames[0]];
}


console.log(getElementsToInteract(buttonsIdArray));

setActivationState(getElementsToInteract(buttonsIdArray), 'disabled');

/* document.getElementById("Button").disabled = true; */
/* download_csv.disabled = true;
download_xlsx.disabled = true;
download_ods.disabled = true;
topo.disabled = true; */

const formatIsSupported = (fileFormat, supportedFormats) => {
  console.log(fileFormat, supportedFormats);
  return supportedFormats.includes(fileFormat);
}

/* Getting header */

/**
 * Returns the header of a worksheet
 * 
 * @param {worksheet} worksheet 
 * @returns array with header names
 */
const getTableHeader = worksheet => {

  const header = [];
  /**
   * range is an object structured like this:
   * s.c = starting column
   * s.r = starting row
   * e.c = ending column
   * e.r = ending row
   * Example:
   * {
      "s": {
          "c": 0,
          "r": 0
      },
      "e": {
          "c": 5,
          "r": 2349
      }
  */
  const range = XLSX.utils.decode_range(worksheet['!ref']);

  let C = range.s.c; // starting column
  const R = range.s.r; // starting row
  // Walk every column in the range
  for(C; C <= range.e.c; ++C) {
    // find the cell in the first row (column name) */
    const cell = worksheet[XLSX.utils.encode_cell({c:C, r:R})]
    // give a name to the columns without one 
    // I give it the column number
    let defaultCellName = "Column_" + (C + 1); // <-- replace with your desired default
    // if the column alredy has a header, use it 
    if (cell && cell.t) {
      defaultCellName = XLSX.utils.format_cell(cell);
    }
    header.push(defaultCellName); // push the name to the headers array
  }
  return header;
}

/**
 * returns metadata of the file
 * @param {object} file 
 * @returns object containing metadata of the file
 */
const getUploadedFileMetadata = file => {
  const size = file.size / 1000;
  const extension = file.name.match(/(\.)(.+)$/)[2];
  //console.log(file);
  return metadata = {
    name: file.name,
    extension: extension,
    size: size,
    date: file.lastModified
  }
}


async function handleFileAsync(e) {

  //let worksheet;
  let wsPreview;
  let header;
  let wsJson;

  const container = document.getElementById(tableID);
  const textContainer = document.getElementById(previewID);


  const file = e.target.files[0];
  if (file === undefined){
    textContainer.innerHTML = '';
    container.innerHTML = '';
    setActivationState(getElementsToInteract(buttonsIdArray), 'disabled');
  } else {
  const matadata = getUploadedFileMetadata(file);
  console.log(metadata);
  console.log(supportedFormats);
  if (!formatIsSupported(metadata.extension, supportedFormats)){
    console.log(supportedFormats);
    textContainer.innerHTML = '<p>Formato non supportato:</p>';
    container.innerHTML = '';
    setActivationState(getElementsToInteract(buttonsIdArray), 'disabled');
  }
  else{
    const data = await file.arrayBuffer(); // data is available only when loaded

    setWorksheet(data, {raw:true, codepage:65001});
    worksheet = getWorksheet(data, {raw:true, codepage:65001});
    wsPreview = getWorksheet(data, {raw:true, codepage:65001, sheetRows: 11});
    //workbook = XLSX.read(data, {raw:true, codepage:65001});
    
    //worksheet = workbook.Sheets[workbook.SheetNames[0]];
    //const wbPreview = XLSX.read(data, {raw:true, codepage:65001, sheetRows: 6});
    //const wbPreview = XLSX.read(data, {codepage:65001, sheetRows: 6});
    //var wsPreview = wbPreview.Sheets[wbPreview.SheetNames[0]];
    header = getTableHeader(worksheet);
    //console.log(header);
    wsJson = XLSX.utils.sheet_to_json(worksheet);


    
    textContainer.innerHTML = '<p>Preview of the first 10 rows of the file:</p>';
    container.innerHTML = XLSX.utils.sheet_to_html(wsPreview);

    // Enable buttons */
    setActivationState(getElementsToInteract(buttonsIdArray), 'active');

    continueUpload(header, wsJson); //try
  }
  }
}

input_dom_element.addEventListener("change", handleFileAsync, false);
download_csv.addEventListener("click", downloadCsv);
download_xlsx.addEventListener("click", downloadXlsx);
download_ods.addEventListener("click", downloadOds);
//topo.addEventListener("click", continueUpload); //try

function downloadCsv(){
  XLSX.utils.sheet_to_csv(worksheet);
  XLSX.writeFile(workbook, 'convert.csv');
}
function downloadXlsx(){
  XLSX.writeFile(workbook, 'convert.xlsx', {bookType: 'xlsx'});
}
function downloadOds(){
  XLSX.writeFile(workbook, 'convert.ods', {bookType: 'ods'});
}

function continueUpload(header, wsJson){
  const valuesToPass = {'header': header, 'dataset': wsJson}
  console.log(valuesToPass);
/*  var xhr = new XMLHttpRequest();
  var url = "http://localhost/progetto_import/dataset?procedure=conceptmapping";
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-Type", "application/json"); */
  
  continue_procedure.value = JSON.stringify(valuesToPass);

  

}
/* let csvtest = XLSX.utils.sheet_to_csv(worksheet2); */
