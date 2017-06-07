<!doctype html>
<html>
<head>
  <title>Experiment</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
</head>
<body>
  <table id="results">

  </table>
</body>
<script>

var saveData = function (filedata, numTrial) {

  var filename = "./" + numTrial + ".txt";
  // send it!
  $.ajax({
    type: 'post',
    cache: false,
    url: './SaveDataTest.php',
    success: responseFn,
    error: responseFn,
    data: {
      filename: filename,
      filedata: filedata,
      nTrial: numTrial
    }
  });
  console.log("Data save attempted.");
};

var responseFn = function (data, status, obj) {
  var n = data;

  var tab = document.getElementById('results');
  var row = tab.rows[Number(n) + 1];
  row.cells[1].innerHTML = status;
};

var genString = function (lengthOfString) {
  var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz01234567890';
  var text = '';
  var i = 0;
  for (i = 0; i < lengthOfString; i++) {
    text += possible.charAt(Math.floor(Math.random() * possible.length));
  }
  return(text);
};

// Generate some random numbers to save in files and print to the screen.
var numTrials = 100;

// Create a table to put results of requests in
var t = document.getElementById("results");
var header = t.createTHead();
headRow = header.insertRow(0);
headRow.insertCell(0).innerHTML = "<b>Trial Number</b>";
headRow.insertCell(1).innerHTML = "<b>Response Code</b>";


for (r=1; r<numTrials+1; r++) {
  var row = t.insertRow(r);
  row.insertCell(0).innerHTML = r;
  row.insertCell(1).innerHTML = '';
};

// Generate and save ~65kb worth of data for each trial.
for (i=0; i < numTrials; i++) {
  var d = genString(66560);
  //debugger;
  saveData(d, i);
};
</script>
</html>
