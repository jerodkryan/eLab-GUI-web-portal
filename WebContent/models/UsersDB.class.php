<?php
class UsersDB {
	
	public static function addUser($user) {
		// Inserts the User object $user into the Users table and returns userId
		$query = "INSERT INTO Users (userName, passwordHash, facebookId)
		                      VALUES(:userName, :passwordHash, :facebookId)";
		$returnId = 0;
		try {
			if (is_null($user) || $user->getErrorCount() > 0)
				throw new PDOException("Invalid User object can't be inserted");
			$db = Database::getDB ();
			$statement = $db->prepare ($query);
			$statement->bindValue(":userName", $user->getUserName());
			$statement->bindValue(":passwordHash", $user->getPasswordHash());
			$statement->bindValue(":facebookId", $user->getFacebookId());
			$statement->execute ();
			$statement->closeCursor();
			$returnId = $db->lastInsertId("userId");
		} catch (Exception $e) { // Not permanent error handling
			echo "<p>Error adding user to Users ".$e->getMessage()."</p>";
		}
		return $returnId;
	}

	public static function getUserRowSetsBy($type = null, $value = null) {
		// Returns the rows of Users whose $type field has value $value
		$allowedTypes = ["userId", "userName", "facebookId"];
		$userRowSets = NULL;
		try {
			$db = Database::getDB ();
			$query = "SELECT userId, facebookId, userName, passwordHash FROM Users";
			if (!is_null($type)) {
			    if (!in_array($type, $allowedTypes))
					throw new PDOException("$type not an allowed search criterion for Users");
			    $query = $query. " WHERE ($type = :$type)";
			    $statement = $db->prepare($query);
			    $statement->bindParam(":$type", $value);
			} else 
				$statement = $db->prepare($query);
			$statement->execute ();
			$userRowSets = $statement->fetchAll(PDO::FETCH_ASSOC);
			$statement->closeCursor ();
		} catch (Exception $e) { // Not permanent error handling
			echo "<p>Error getting user rows by $type: " . $e->getMessage () . "</p>";
		}
		return $userRowSets;
	}
	
	public static function updateUser($user) {
		// Updates user by
		$query = "UPDATE Users SET passwordHash= :passwordHash WHERE userId = :userId";
		$returnId = 0;
		try {
			if (is_null($user) || $user->getErrorCount() > 0)
				throw new PDOException("Invalid Userdata object can't be inserted");
				$db = Database::getDB ();
				$statement = $db->prepare ($query);
				$statement->bindValue(":passwordHash", $user->getPasswordHash());
				$statement->bindValue(":userId", $user->getUserId());
				$statement->execute ();
				$statement->closeCursor();
		} catch (Exception $e) { // Not permanent error handling
			echo "<p>Error adding user to Users ".$e->getMessage()."</p>";
		}
		$returnId = $user->getUserId();
		return $returnId;
	}
	
	public static function getUsersArray($rowSets) {
		// Returns an array of User objects extracted from $rowSets
		$users = array();
	 	if (!empty($rowSets)) {
			foreach ($rowSets as $userRow ) {
				$user = new User($userRow);
				$user->setUserId($userRow['userId']);
				$user->setFacebookId($userRow['facebookId']);
				array_push ($users, $user );
			}
	 	}
		return $users;
	}
	
	public static function getUsersBy($type=null, $value=null) {
		// Returns User objects whose $type field has value $value
		$userRows = UsersDB::getUserRowSetsBy($type, $value);
		return UsersDB::getUsersArray($userRows);
	}
	
	public static function getUserValues($rowSets, $column) {
		// Returns an array of values from $column extracted from $rowSets
		$userValues = array();
		foreach ($rowSets as $userRow )  {
			$userValue = $userRow[$column];
			array_push ($userValues, $userValue);
		}
		return $userValues;
	}
	
	public static function getUserValuesBy($column, $type=null, $value=null) {
		// Returns the $column of Users whose $type field has value $value
		$userRows = UsersDB::getUserRowSetsBy($type, $value);
		return UsersDB::getUserValues($userRows, $column);
	}
}
?>