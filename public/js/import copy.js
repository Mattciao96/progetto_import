const tableID = 'tbl-data';
const previewID = 'preview-text';
let worksheet;
let workbook;
let header;
let wsj;

/* document.getElementById("Button").disabled = true; */
download_csv.disabled = true;
download_xlsx.disabled = true;
download_ods.disabled = true;



/* per l'header */
function getTableHeader(sheet) {
  var headers = [];
  var range = XLSX.utils.decode_range(sheet['!ref']);
  var C, R = range.s.r; /* start in the first row */
  /* walk every column in the range */
  for(C = range.s.c; C <= range.e.c; ++C) {
      var cell = sheet[XLSX.utils.encode_cell({c:C, r:R})] /* find the cell in the first row */

      var hdr = "NA_" + C; // <-- replace with your desired default 
      if(cell && cell.t) hdr = XLSX.utils.format_cell(cell);

      headers.push(hdr);
  }
  return headers;
}


getTableHeader = worksheet => {
  return get_header_row
}

async function handleFileAsync(e) {
  const file = e.target.files[0];
  const data = await file.arrayBuffer();

  /* data is an ArrayBuffer */
  /* raw:true, codepage:65001 are necessary to parse csv */
  /* const workbook = XLSX.read(data, {sheetRows: 11, raw:true, codepage:65001});
  var worksheetPreview = workbook.Sheets[workbookPreview.SheetNames[0]];
 */
  workbook = XLSX.read(data, {raw:true, codepage:65001});
  
  worksheet = workbook.Sheets[workbook.SheetNames[0]];
  const wbPreview = XLSX.read(data, {raw:true, codepage:65001, sheetRows: 6});
  //const wbPreview = XLSX.read(data, {codepage:65001, sheetRows: 6});
  var wsPreview = wbPreview.Sheets[wbPreview.SheetNames[0]];
  header = get_header_row(worksheet);
  console.log(header);
  wsj = XLSX.utils.sheet_to_json(worksheet);
  console.log(wsj);
  var container = document.getElementById(tableID);
  var textContainer = document.getElementById(previewID);
  textContainer.innerHTML = '<p>Preview of the first 10 rows of the file:</p>';
 
  container.innerHTML = XLSX.utils.sheet_to_html(wsPreview);

  /* Reenables buttons */
  download_csv.disabled = false;
  download_xlsx.disabled = false;
  download_ods.disabled = false;

  continueUpload(); //try

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

function continueUpload(){
  const valuesToPass = {'header': header, 'dataset': wsj}
  console.log(valuesToPass);
  // RIMOSSO, NON FUNZIONA
/*   var xhr = new XMLHttpRequest();
  var url = "http://localhost/progetto_import/dataset?procedure=conceptmapping";
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-Type", "application/json");
   */
  continue_procedure.value = JSON.stringify(valuesToPass);

  

}
/* let csvtest = XLSX.utils.sheet_to_csv(worksheet2); */
