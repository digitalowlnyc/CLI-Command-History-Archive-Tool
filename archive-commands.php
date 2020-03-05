<?php

	define("SCRIPT_LABEL", "BND");
	define("META_FILE_NAME", ".BND_PROJECT_METAFILE");
	define("HISTORY_FILE_NAME", ".bnd_command_history");

	function findProjectRoot() {
		$dir = getcwd();

		$dirComponents = explode("/", $dir);

		while(!empty($dirComponents)) {

			if(count($dirComponents) === 1) {
				if($dirComponents[0] === "") {
					$currentDir = "/";
				}
			} else {
				$currentDir = implode("/", $dirComponents);
			}

			$file = $currentDir . "/" . META_FILE_NAME;

			if(file_exists($file)) {
				return [$currentDir, $file];
			}

			array_pop($dirComponents);
		}

		return null;
	}

	$history = [];
	while($f = fgets(STDIN)){
    	//echo "HISTORY: $f";
    	preg_match("#\d+\s+(.+)#", $f, $matches);

    	$command = $matches[1];

    	preg_match('#([A-Za-z0-9_~-]+)(\s+.+)?#', $command, $commandMatches);

    	if(count($commandMatches) < 2) {
    		print_r($commandMatches);
    		throw new RuntimeException("Could not determine executable: " . $command);
		}

    	$executable = $commandMatches[1];

    	if($command === "cd ."
		|| $command === "cd ~"
		|| $command === "env"
		|| $executable === "git"
		|| $executable === "ls"
		|| $command === "history"
		) {
    		continue;
		}

    	$history[] = $command;
	}

	function getCommandHistory($directory = ".") {
		$filePath = $directory . "/" . HISTORY_FILE_NAME;

		if(!file_exists($filePath)) {
			return [];
		}

		$contents = file_get_contents($filePath);

		$data = json_decode($contents, true);

		return $data;
	}

	function setCommandHistory($data, $directoryToSaveIn = ".") {
		$str = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

		$filePath = $directoryToSaveIn . "/" . HISTORY_FILE_NAME;

		file_put_contents($filePath, $str);

		echo "Saved to: " . $filePath . PHP_EOL;
	}

	$projectRoot = findProjectRoot();

	if($projectRoot === null) {
		$projectRoot = ".";
	} else {
		$projectRoot = $projectRoot[0];
		echo "Project root found: " . $projectRoot . PHP_EOL;
	}

	$commandHistory = getCommandHistory($projectRoot);

	$uniqueHistory = array_unique($history);

	$newCommandHistory = array_merge($commandHistory, $uniqueHistory);

	$newCommandHistory = array_unique($newCommandHistory);

	setCommandHistory($newCommandHistory, $projectRoot);

	echo "Saved " . (count($newCommandHistory) - count($commandHistory)) . " new commands" . PHP_EOL;
