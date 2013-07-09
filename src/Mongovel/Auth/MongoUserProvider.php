<?php namespace Mongovel\Auth;

use Hash;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use MongoRegex;
use User;

class MongoUserProvider implements UserProviderInterface
{
	public function retrieveByID($identifier)
	{
		$user = User::findOne($identifier);

		return $user;
	}


	public function retrieveByCredentials(array $credentials)
	{
		$query = $credentials;
		unset($query['password']);

		if (isset($query['username'])) {
			$query['username'] = new MongoRegex('/' . $query['username'] . '/i');
		}

		$user = User::findOne($query);

		return $user;
	}


	public function validateCredentials(UserInterface $user, array $credentials)
	{
		$user = $this->retrieveByCredentials($credentials);

		if ($user) {
			return Hash::check($credentials['password'], $user->password);
		}
		else {
			return false;
		}
	}
}
