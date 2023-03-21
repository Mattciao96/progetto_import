//console.log(mappingValues);
/**
 * ! COSE DA FIXARE
 * 
 * FATTO ! SPOSTA EMPTY CHECK 
 * FATTO CONTROLLA IL NUMERO DI CAMPI, PER RENDERLO PIÙ FACILE DA MODIFICARE
 * FATTO ! CONTROLLA ESCAPE
 * FATTO ! CHECK BROWSER
 * 
 * FATTO IN UN BEL MODO ! TENTA DI GESTIRE GLI 0
 * 
 * 
 * 
 */
/************************************************************************************************
  *  Example:
  * {
  *   <concept-1> :
  *   {
  *     mappedConcepts : [<mappedConcept-1>, <mappedConcept-1>]
  *     examples : 
  *     {
  *       <mappedConcept-1>: [<example-1, example2>],
  *       <mappedConcept-2>: [<example-1, example2>],
  *     }
  *     numberOfMappedConcepts: <number>,
  *     numberOfMappedFields: <number>,
  *     addCount: <number>,
        removeCount: <number>,
  *   },
  *   <concept-2> :
  *   {...}
  * 
  * 
  * I need:
  * - database concepts
  * - build structure 
  * 
  * 
  * 
  ************************************************************************************************/

class ConceptMap {

  fieldsLimit = 3;
  conceptsObj = {};
  examplesObj;
  jsonToPass;


  
  setConceptSctructure(conceptJson) {
    conceptJson.forEach(element => {
      this.conceptsObj[element] = {
        mappedConcepts: Array(this.fieldsLimit).fill(''),
        numberOfMappedConcepts: 0,
        numberOfMappedFields: 1,
        addCount: 1,
        removeCount: 0,
        examples: [Array(this.fieldsLimit).fill([])],
        examplesCombined:[]
      }
    });
  }

  setExamples(examplesObj){
    this.examplesObj = examplesObj;

  }


  getConceptObj() {
    console.log(this.conceptsObj);
    return this.conceptsObj;
  }



  modifyCount(concept, propriety, operation){
    if (operation === 'add'){
      this.conceptsObj[concept][propriety]++;
    } else if (operation === 'subtract'){
      this.conceptsObj[concept][propriety]--;
    } else {
      console.log('ERROR: operation not supported');
    }
  }
  
  getProprietyValue(concept, propriety){
    
    console.log(this.conceptsObj[concept][propriety]);
    return this.conceptsObj[concept][propriety];
  }

  addMappedConcept(fieldConcept, fieldNumber, mappedConcept){
    this.conceptsObj[fieldConcept]['mappedConcepts'][fieldNumber] = mappedConcept;
    this.conceptsObj[fieldConcept]['examples'][fieldNumber] = mappedConcept !== '' ? this.examplesObj[mappedConcept] : ['','',''];
    this.combineExamples(fieldConcept);
  }

  removeMappedConcept(fieldConcept, fieldNumber){
    this.conceptsObj[fieldConcept]['mappedConcepts'][fieldNumber] = '';
    console.log(fieldNumber);
    console.log(this.conceptsObj[fieldConcept]['mappedConcepts']);
    this.conceptsObj[fieldConcept]['examples'][fieldNumber] = [];
    this.combineExamples(fieldConcept);
    console.log(this.conceptsObj[fieldConcept]);
  }

  combineExamples(fieldConcept){
    this.conceptsObj[fieldConcept]['examplesCombined'] = this.removeSpaces(this.sumArrays(this.conceptsObj[fieldConcept]['examples']));
  }

  esamplesToString(fieldConcept, fieldNumber){

  }


  // GODLIKE
  sumArrays(...array) {
    const arrays = array.flat();
    console.log('-')
    console.log(arrays)
    console.log('-')
    const n = arrays.reduce((max, xs) => Math.max(max, xs.length), 0);
    console.log('-')
    console.log(n)
    console.log('-')
    const result = Array.from({ length: n });
    return result.map((_, i) => arrays.map(xs => xs[i] || (xs[i] === 0 ?'0': '')).reduce((sum, x) => sum + ' ' + x));
  }

  removeSpaces(array){
    console.log(array);
    let results = array.map(element => {
      let el = '' + element;
      /* console.log(`TRU ${element}`);
      if (element === 0){
        element = '0';
      }
      let el = '' + element;
      console.log(`EL: ${el}`); */

      // numbers must be changed to strings
      /* if (typeof(el) === 'number' | el === 0 | el === '0'){
        const digit_number = this.numDigits(el);
        console.log(`DIGITS: ${digit_number}`)
        el = el.toString().padStart(digit_number + 1, '0');
      } */
      el = el.trim();
      el = el.replace(/\s{2,}/g, ' ');
      return element;
    });
    console.log(results);
    return results
  }

  // function to get number of digits of a number
  numDigits(x) {
    return (Math.log10((x ^ (x >> 31)) - (x >> 31)) | 0) + 1;
  }

  checkMappedConcepts(fieldConcept){
    console.log(this.conceptsObj[fieldConcept]['mappedConcepts']);
    if (this.conceptsObj[fieldConcept]['mappedConcepts'].every( (val, i, arr) => val === '' )){
      return false;
    }
    else{
      return true;
    } 
  }


  /**
   * Pass only the mapped concepts
   */
  createJsonToPass(){
    let jsonToPass = {};
    const entries = Object.entries(this.conceptsObj)
    for (const [key, value] of entries) {
      //console.log(value['mappedConcepts']);
      if (!(value['mappedConcepts'].every( (val, i, arr) => val === '' ))){
        console.log(value['mappedConcepts']);
        jsonToPass[key] = value['mappedConcepts'];
      }
    }
    this.jsonToPass = jsonToPass;
  }

}
  




// modify to change the max number of fields


// function to add element before and after
// use in this way:
//newElement.appendBefore(element);
//newElement.appendAfter(element);

Element.prototype.appendBefore = function (element) {
  element.parentNode.insertBefore(this, element);
},false;
Element.prototype.appendAfter = function (element) {
  element.parentNode.insertBefore(this, element.nextSibling);
},false;

/*
function sumArrays(...array) {
  const arrays = array.flat();
 
  const n = arrays.reduce((max, xs) => Math.max(max, xs.length), 0);
  const result = Array.from({ length: n });
  return result.map((_, i) => arrays.map(xs => xs[i] || '').reduce((sum, x) => sum + ' ' + x));
}

 Array.prototype.insert = function ( index, item ) {
  this.splice( index, 0, item );
};

var index = items.indexOf(3452);

if (index !== -1) {
    items[index] = 1010;
}var index = items.indexOf(3452);


if (matches) {
    number = matches[0];
}

 */

/**
 * Returns an array:
 * [
 *  1 => concept
 *  2 => number
 * ]
 */
function getConceptAndNumber(string){
  const regex = /^(.*)-(\d+)$/;
  return string.match(regex);
}

//console.log(mappingValues);
let conceptsObj = new ConceptMap();
conceptsObj.setConceptSctructure(mappingValues[0]);
let concepts = conceptsObj.getConceptObj();
const fieldsLimit = conceptsObj.fieldsLimit;

examples = mappingValues[2];
conceptsObj.setExamples(examples);


function addField(event){
  
  const concept = event.target.parentElement;
  //console.log(concept.querySelector('select'));
  
  selectForm = concept.querySelector('select'); // outdated
  addButton = concept.querySelector('#add');

  //try
  selectForm = addButton.previousElementSibling;

  fieldId = selectForm.id;
  fieldConcept = getConceptAndNumber(fieldId)[1];
  fieldNumber = parseInt(getConceptAndNumber(fieldId)[2]);
  newFieldNumber = fieldNumber + 1;

  // count the number of actual select fields
  fieldsCount = conceptsObj.getProprietyValue(fieldConcept, 'numberOfMappedFields');
  if (fieldsCount >= fieldsLimit){
    alert(`ERROR: You can\'t have more than ${fieldsCount} fields`);
  }
  else {
  newelement = selectForm.cloneNode(true);
  newelement.setAttribute('id', fieldConcept + '-' + newFieldNumber);
  newelement.appendBefore(addButton);
  rmvButtonCount = conceptsObj.getProprietyValue(fieldConcept, 'removeCount');

 

  // add a REMOVE button only if there isn't one alredy
  if (rmvButtonCount === 0){
    let removeButton = createRemoveButton();
    removeButton.appendAfter(addButton);
    conceptsObj.modifyCount(fieldConcept, 'removeCount', 'add')

  }
  

  conceptsObj.modifyCount(fieldConcept, 'numberOfMappedFields', 'add')
  conceptsObj.getProprietyValue(fieldConcept, 'numberOfMappedFields');
  }
}

function removeField(event){
  
  const concept = event.target.parentElement;
  //console.log(concept.querySelector('select'));
  
  // 1: get the id of concept of the id and the number
  selectForm = concept.querySelector('select');
  fieldId = selectForm.id;
  fieldConcept = getConceptAndNumber(fieldId)[1];
  // count the number of actual select fields
  fieldsCount = conceptsObj.getProprietyValue(fieldConcept, 'numberOfMappedFields');

  if (fieldsCount <= 1){
    alert('ERROR: You can\'t have less than one field');
  }
  else {
    
    // 2: combine the fieldsCount with the fieldId to selet and remove only the last form
    fieldToRemoveId = fieldConcept + '-' + (parseInt(fieldsCount));
    selectFormToRemove = concept.querySelector('#' + fieldToRemoveId);


    // 3: decrease the number of fields in trhe json
    conceptsObj.modifyCount(fieldConcept, 'numberOfMappedFields', 'subtract')
    newFieldsCount = conceptsObj.getProprietyValue(fieldConcept, 'numberOfMappedFields');
    indexToRemove = parseInt(fieldsCount) - 1;
    conceptsObj.removeMappedConcept(fieldConcept, indexToRemove);

    // 4: redo the examples
    // the new list is displayed only if the array of mapped concept isn't empty
    // in this way if you choose the option: no corrispondence, the list of examples isn't displayed
    removeExistingList(concept);
    if (conceptsObj.checkMappedConcepts(fieldConcept)){
      let list = addExampleList(conceptsObj.conceptsObj[fieldConcept]['examplesCombined']);
      concept.appendChild(list);
    }
    

    if (newFieldsCount <= 1){
      rmvButton = concept.querySelector('#remove');
      concept.removeChild(rmvButton);
      conceptsObj.modifyCount(fieldConcept, 'removeCount', 'subtract')
  
    }

    // 4: remove the field
    concept.removeChild(selectFormToRemove);

  }
}


function createRemoveButton(){
  let removeButton = document.createElement("button");
  removeButton.innerHTML = 'Remove';
  removeButton.type = 'button';
  removeButton.setAttribute("id","remove");
  removeButton.setAttribute("onclick","removeField(event)");
  return removeButton;
}



function addValue(event){

  

  // get values
  const concept = event.target.parentElement;
  fieldId = event.target.id;
  fieldConcept = getConceptAndNumber(fieldId)[1];
  fieldNumber = parseInt(getConceptAndNumber(fieldId)[2]) - 1;
  mappedConcept = event.target.value;
  

  conceptsObj.addMappedConcept(fieldConcept, fieldNumber, mappedConcept);
  //console.log(conceptsObj.conceptsObj[fieldConcept]);
  //console.log("Value: " + event.target.value + "; Display: " + event.target[event.target.selectedIndex].text + ".");
  //console.log(`<p>Esempi:${mappingValues[2][event.target.value]}<p>`);

  // remove the old  examples list (if exists) and add a new one
  // the new one is added only if the array of mapped concept isn't empty
  // in this way if you choose the option: no corrispondence, the list of examples isn't displayed
  removeExistingList(concept);
  if (conceptsObj.checkMappedConcepts(fieldConcept)){
    let list = addExampleList(conceptsObj.conceptsObj[fieldConcept]['examplesCombined']);
    concept.appendChild(list);
  }
    
}
// seleziono tutti e faccio correre sta funzione
function addValueDefault(element){
  

  // get values
  const concept = element.parentElement;
  fieldId = element.id;
  fieldConcept = getConceptAndNumber(fieldId)[1];
  fieldNumber = parseInt(getConceptAndNumber(fieldId)[2]) - 1;
  mappedConcept = element.value;
  

  conceptsObj.addMappedConcept(fieldConcept, fieldNumber, mappedConcept);
  //console.log(conceptsObj.conceptsObj[fieldConcept]);
  //console.log("Value: " + event.target.value + "; Display: " + event.target[event.target.selectedIndex].text + ".");
  //console.log(`<p>Esempi:${mappingValues[2][event.target.value]}<p>`);

  // remove the old  examples list (if exists) and add a new one
  // the new one is added only if the array of mapped concept isn't empty
  // in this way if you choose the option: no corrispondence, the list of examples isn't displayed
  removeExistingList(concept);
  if (conceptsObj.checkMappedConcepts(fieldConcept)){
    let list = addExampleList(conceptsObj.conceptsObj[fieldConcept]['examplesCombined']);
    concept.appendChild(list);
  }
    
}

// SPOSTATA NELLA CLASSE
/* function checkMappedConcepts(fieldConcept){
  console.log(conceptsObj.conceptsObj[fieldConcept]['mappedConcepts']);
  if (conceptsObj.conceptsObj[fieldConcept]['mappedConcepts'].every( (val, i, arr) => val === '' )){
    return false;
  }
  else{
    return true;
  } 
} */

function addExampleList(array) {
  console.log(array);
  ul = document.createElement('ul');
  array.forEach(function (item) {
    let li = document.createElement('li');
    ul.appendChild(li);
    li.innerHTML += item;
  });
  return ul;
}

function removeExistingList(partOfDocument) {
  try{
  var elem = partOfDocument.querySelector('ul');
  elem.parentNode.removeChild(elem);
  } catch{
    console.log('element not found');
  }
}
// ottimo

// console.log(mappingValues[3]);

function passConceptMapping(){
  conceptsObj.createJsonToPass();
  mappingJson = conceptsObj.jsonToPass;
  toPass = {'map':mappingJson, 'dataset':mappingValues[3]}
  //console.log(mappingJson);
  
  //continue_procedure.value = JSON.stringify({'mapping': mappingJson});
  //console.log(continue_procedure);

  var xhr = new XMLHttpRequest();
  var url = "http://localhost/progetto_import/dataset?procedure=conceptmapping";
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-Type", "application/json");
  

  continue_procedure.value = JSON.stringify(toPass);

}

selectElements = document.querySelectorAll('select');
selectElements.forEach((select) => {
  if (select.value !== 'NO CORRISPONDENCE') {
    addValueDefault(select);
  }
});