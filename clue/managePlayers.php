<?php 

require './include/clueDbHeader.php'; 
session_start();

?>

<!DOCTYPE html>

<html>

<head>
<?php
require './include/bootstrapHeader.php';
?>
<title>Clue - Player Management</title>
<script>
function removePlayer(id_number, username, hasGames) {
	// if they have games
	if (hasGames == true) {
		if (confirm("Player " + username + " cannot be deleted unless they are not in any games.\n"
			 + "Do you want to go to game management?") == true) {
			window.location = "/clue/manageGames.php";
		}
	}

	// if they don't have games
	if (hasGames == false) {
		// confirm dialog
		if (confirm("Remove player " + username + "?\nThis cannot be undone.") == true) {

			// actually do the delete here, it will redirect back when it is done
			window.location = "/clue/removePlayer.php?userid=" + id_number;
		}
	}
}

</script>

</head>

<body>
<div class="container">
<h1>Manage Players <a href="/clue/newPlayer.php" class="btn btn-lg btn-success">Add New Player</a></h1>

<?php

if ($status === true) {
	try {

	// Query for the list of users
	$query = "SELECT id, username FROM players ORDER BY username";
	
	$statement = $db->prepare($query);
	$statement->execute();

	foreach ($statement->fetchAll() as $row) {
		echo '<div class="list-group">';
		echo '<li class="list-group-item active">' . $row['username'] . "</li>\n";

		// get the list of games they are currently in
		$gameQuery = "SELECT g.game_number, s.name AS player_character, s.color FROM games g " .
			"JOIN players p ON g.player_id=p.id " .
			"JOIN suspect s ON g.player_character=s.id " .
			"WHERE p.id=:id ORDER BY game_number";
		$gameStatement = $db->prepare($gameQuery);
		$gameStatement->bindValue(':id', $row['id']);

		$gameStatement->execute();

		$hasGames = "false";
		if ($gameStatement->rowCount() > 0) {
			$hasGames = "true";
		}

		foreach ($gameStatement->fetchAll() as $gameRow) {
			$isWhite = "";
			if ($gameRow["color"] == "F8F8FF") {
				$isWhite = "text-shadow: 0 0 6px #000000, 0 0 1px #000000, 0 0 3px #000000;";
			}

			echo '<li class="list-group-item" >Game #' . $gameRow["game_number"] . ' - ' .
				'<p style="' . $isWhite . 'font-weight: bold; color:#' . $gameRow["color"] . '" >' . $gameRow["player_character"] . '</p></li>';
		}


		echo '<button class="btn btn-block list-group-item list-group-item-danger" onClick="removePlayer(\'' . $row['id'] . '\', \'' . $row['username'] . '\', ' . $hasGames . ')" >Delete ' . $row['username'] . '</button>' . "\n";
		echo '</div>';	
	}

	} catch (PDOEXCEPTION $ex) {
		echo "Something bad happened, details are: " . $ex;
	}


} else {
	echo '<h1>Database connection failed!</h1>' . "\n";
}


?>
<a href="/clue/clue.php" class="btn btn-lg btn-success">Back</a>
<a href="/clue/clue.php" class="btn btn-lg btn-warning">Home</a>
</div>

<?php
require './include/bootstrapFooter.php';
?>
</body>

</html>
