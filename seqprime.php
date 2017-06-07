<?php
// include the functions to get the pid, and get it.
require_once("./Resources/pInfo.php");
 ?>

<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Experiment</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/seedrandom/2.4.2/seedrandom.min.js"></script>
	<script src="jspsych-5.0.3/jspsych.js"></script>
  <script src="jspsych-5.0.3/plugins/jspsych-sequential-priming.js"></script>
  <script src="jspsych-5.0.3/plugins/jspsych-text.js"></script>
  <script src="jspsych-5.0.3/plugins/jspsych-call-function.js"></script>
  <script src="Util.js"></script>
	<link href="jspsych-5.0.3/css/jspsych.css" rel="stylesheet" type="text/css"></link>
</head>
<body>

</body>
<script>
  // Define vars
  var hiConLst, loConLst, gunFls, noGunFls, seed, pid, taskTimeline;
  var leftKeyCode, rightKeyCode, correct_answer, gunKeyCode, nogunKeyCode;
  var hiConFls, loConFls, gunLst, noGunFls, mask, redX, check, expPrompt;
  var instr1, instructStim, countdown, countdownNumbers;
  var timeline = [];
  var numTrials = 220;
  var timing_parameters = [200, 200, 200, 300];

  // Data saving utility variables
  var numSaveAttempts = 0;
  var maxSaveAttempts = 5;
  var saveSuccessCode = -1;
  var savedLocalStorage = -1;
  var tryingToSave = false;
  var firstSaveCallTime = 0;
  var saveSuccessTime = 0;

  // The timing_parameters should correspond to the planned set of stimuli.
  // In this case, I'm leading with a mask (following Ito et al.), and then
  // the prime, and then the stimulus, and then the mask until the end of the
  // trial.

  // get a pid from a list of unused pids.

  var d = new Date();
  var seed = d.getTime();
  Math.seedrandom(seed);

  // get the pid, choose keys:
  <?php
  // Choose the keys.
  $cond = mt_rand(0,1);
  if ($cond) {
    $gunKey = "e";
    $nogunKey = "i";
  } else {
    $gunKey = "i";
    $nogunKey = "e";
  }

  // Get the pid:
  if ($_GET['test']) {
    echo "console.log('GET worked');";
    echo "var test=true;";
    $pid = getNewTestingPID("./Resources/testPID.csv", $gunKey, $nogunKey);
  } else {
    echo "var test=false;";
    $pid = getNewPID("./Resources/PID.csv", $gunKey, $nogunKey);
  }


  // put the variables out
  echo "gunKey = '".$gunKey."';";
  echo "nogunKey = '".$nogunKey."';";
  echo "pid = '".$pid."';";
  ?>

  // get leading zeros
  var pidStr = "00" + pid;
  pidStr = pidStr.substr(pidStr.length - 3);

  var filename = "../data/" + pidStr + "_" + seed + ".csv";

  // Choose keys:
  leftKey = "e";
  rightKey = "i";
  leftKeyCode = jsPsych.pluginAPI.convertKeyCharacterToKeyCode(leftKey);
  rightKeyCode = jsPsych.pluginAPI.convertKeyCharacterToKeyCode(rightKey);
  gunKeyCode = jsPsych.pluginAPI.convertKeyCharacterToKeyCode(gunKey);
  nogunKeyCode = jsPsych.pluginAPI.convertKeyCharacterToKeyCode(nogunKey);
  leftObj = gunKeyCode == leftKeyCode ? "GUN" : "NO GUN";
  rightObj = gunKeyCode == rightKeyCode ? "GUN" : "NO GUN";

  if ((leftObj != "GUN" && leftObj != "NO GUN") || (rightObj != "GUN" && rightObj != "NO GUN")) {
    throw "keys are bad!";
  }

  // Append pid and condition information to all trials.
  jsPsych.data.addProperties({
    pid: pid,
    seed: seed,
    gun_key: gunKey,
    nogun_key: nogunKey,
    left_obj: leftObj,
    right_obj: rightObj
  });

  // Save data function
  var saveData = function () {
    if (firstSaveCallTime==0) {
      firstSaveCallTime = new Date().getTime();
    }
    tryingToSave = true;
    var filedata = jsPsych.data.dataAsCSV;
    // send it!
  	$.ajax({
  		type: 'post',
  		cache: false,
  		url: './Resources/SaveData.php',
  		data: {
  			filename: filename,
  			filedata: filedata
  		},
      success: fileSaved,
      error: saveError
  	});
    numSaveAttempts++;
    console.log("Data save attempted.");
  };

  var fileSaved = function (data, textStatus, jqXHR) {
    saveSuccessCode = 0;
    saveSuccessTime = new Date().getTime();
    console.log(textStatus);
  };

  var saveError = function (data, textStatus, jqXHR) {
    console.log(textStatus);
    if (numSaveAttempts < maxSaveAttempts) {
      saveData();
    } else {
      // Try the local storage thing.
      if (storageAvailable('localStorage')) {
        try {
          localStorage.setItem(pid, jsPsych.data.dataAsJSON());
          savedLocalStorage = 0; // successful local storage attempt
        } catch(e) {
          // unnsuccessful save attempt but it appeared to be available
          savedLocalStorage = 2;
        }
      }
      else {
        // unsuccessful localStorage save attempt because it is not available.
        savedLocalStorage = 1;
      }
    }
  };

  // Load instruction strings
  if (gunKeyCode == 69) {
    instr1 = <?php
    $myfile = fopen("./Texts/InstructionsScreen1e-gun.txt", "r") or die("Unable to open file!");
    echo json_encode(fread($myfile,filesize("./Texts/InstructionsScreen1e-gun.txt")));
    fclose($myfile);
    ?>

  } else {
    instr1 = <?php
    $myfile = fopen("./Texts/InstructionsScreen1i-gun.txt", "r") or die("Unable to open file!");
    echo json_encode(fread($myfile,filesize("./Texts/InstructionsScreen1i-gun.txt")));
    fclose($myfile);
    ?>

  }

  // Make the expPrompt
  expPrompt = '<table style="width:100%">'
  + '<tr> <th>"' +
  leftKey + '" key: ' + leftObj + '</th> <th>' +
  '"' + rightKey + '" key: ' + rightObj + '</th> </tr>' + '</table>';

  // Make the intro screen which includes pid:
  introStim = {
    type: 'text',
    text: '<p>For the experimenter.</p>\
    <p style="font-size:150%">PID: ' + pid + '</p>\
    <p>Press the spacebar to show participant instructions.</p>',
    cont_key: [32]
  };

  // Make the instruction stimulus.
  instructStim = {
    type: "text",
    text: instr1,
    cont_key: [32]
  };

  // Make a countdown sequence to begin the task
  countdownNumbers = [
    '<div id="jspsych-countdown-numbers">3</div>',
    '<div id="jspsych-countdown-numbers">2</div>',
    '<div id="jspsych-countdown-numbers">1</div>'
  ]
  countdown = {
    type: "sequential-priming",
    stimuli: countdownNumbers,
    is_html: true,
    choices: [],
    prompt: expPrompt,
    timing: [1000, 1000, 1000],
    response_ends_trial: false,
    feedback: false,
    timing_post_trial: 0,
    iti: 0
  };

  // Load stimulus lists

  // faces:
  hiConFls = <?php echo json_encode(glob("./HiCon/*.png")); ?>

  loConFls = <?php echo json_encode(glob("./LoCon/*.png")); ?>

  // objects:

  gunFls = <?php echo json_encode(glob("./GrayGuns/*.png")); ?>

  noGunFls = <?php echo json_encode(glob("./GrayNonguns/*.png")); ?>

  // Put the stimuli in lists with the relevant information.
  gunLst = [];
  noGunLst = [];
  hiConLst = [];
  loConLst = [];

  var makeStimObjs = function (fls, condVar, condValue) {
    tempLst = [];
    for (i=0; i<fls.length; i++) {
      fl = fls[i];
      flVec = fl.split("/");
      tempObj = {
        file: fl,
        stId: flVec[flVec.length-1]
      };
      tempObj[condVar] = condValue;
      tempLst.push(tempObj);
    }
    return(tempLst);
  };

  hiConLst = makeStimObjs(hiConFls, "contrast", "high");
  loConLst = makeStimObjs(loConFls, "contrast", "low");
  gunLst = makeStimObjs(gunFls, "objectType", "gun");
  noGunLst = makeStimObjs(noGunFls, "objectType", "nogun");

  mask = "MaskReal.png";
  redX = "XReal.png";
  check = "CheckReal.png";
  tooSlow = "TooSlow.png";
  blank = "Blank.png"

  // utility sum function
  var sum = function (a, b) {
    return a + b;
  };

  // Randomize the order of trials, but recycle the list first, and randomly
  // choose what's needed in the remaining.
  objs = randomRecycle(gunLst, numTrials/2).concat(randomRecycle(noGunLst, numTrials/2));
  objs = rndSelect(objs, objs.length);

  faces = randomRecycle(hiConLst, numTrials/2).concat(randomRecycle(loConLst, numTrials/2));
  faces = rndSelect(faces, faces.length);

  // Make all the trials and timelines.
  taskTrials = {
    type: "sequential-priming",
    choices: [leftKeyCode, rightKeyCode],
    prompt: expPrompt,
    timing_stim: timing_parameters,
    response_ends_trial: true,
    timeline: [],
    timing_response: timing_parameters[2] + timing_parameters[3],
    response_window: [timing_parameters[0] + timing_parameters[1], Infinity],
    feedback: true,
    feedback_duration: 1000,
    correct_feedback: check,
    incorrect_feedback: redX,
    timeout_feedback: tooSlow,
    timing_post_trial: 0,
    iti: 1000
  };

  for (i=0; i<numTrials; i++){
    correct_answer = objs[i].objectType == 'gun' ? gunKeyCode : nogunKeyCode;
    tempTrial = {
      stimuli: [mask, faces[i].file, objs[i].file, mask],
      data: {
        constrast: faces[i].contrast,
        obj_type: objs[i].objectType,
        face_id: faces[i].stId,
        obj_id: objs[i].stId
      },
      correct_choice: correct_answer
    };
    taskTrials.timeline.push(tempTrial);
  }

  // save the data before the thank you screen.
  saveCall = {
    type: "call-function",
    func: saveData
  };


  // Push everything to the big timeline in order
  timeline.push(introStim);
  timeline.push(instructStim);
  timeline.push(countdown);
  timeline.push(taskTrials);
  timeline.push(saveCall);
  //timeline.push(thankyouTrial);
  //debugger;

  var endExp = function() {

    var doEnd = function () {
      var saveTime = saveSuccessTime - firstSaveCallTime;
      // Build the url with all the things in it
      var baseUrl = "https://cuboulder.qualtrics.com/jfe/form/SV_4MidnvWMoO2mNr7?";
      var pidUrl = "pid=" + pid;
      var saveUrl = "&saveSuccessCode=" + saveSuccessCode;
      var localStoreUrl = "&localStorageCode=" + savedLocalStorage;
      var numSaveTries = "&numSaveAttempts=" + numSaveAttempts;
      var saveTimeUrl = "&saveTimeMs=" + saveTime;
      window.location =  baseUrl + pidUrl + saveUrl + localStoreUrl +
      numSaveTries + saveTimeUrl;
    };

    if (!(test & saveSuccessCode !== 0)) {

      if (!tryingToSave) {
        // The save data function hasn't been called yet, so call it.
        saveData();
      }

      if (saveSuccessCode!==0 && numSaveAttempts < maxSaveAttempts) {
        // If we haven't successfully saved, but we have tries left, just wait
        // and try to end again in 1 second.
        window.setTimeout(endExp, 1000);
      }
      else {
        doEnd();
      }
    }
  };

  // try to set the background-color
  document.body.style.backgroundColor = '#d9d9d9';

  jsPsych.init({
  	timeline: timeline,
    fullscreen: true,
  	on_finish: endExp
  });

</script>
</html>
