<?php namespace Mongovel\Auth;

use Hash;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use MongoRegex;
use User;

class MongoUserProvider implements UserProviderInterface
{
	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByID($identifier)
	{
		$user = User::findOne($identifier);

		return $user;
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByCredentials(array $credentials)
	{
		$query = $credentials;
		unset($query['password']);

		if (isset($query['username'])) {
			$query['username'] = new MongoRegex('/^' . $query['username'] . '$/i');
		}

		$user = User::findOne($query);

		return $user;
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Illuminate\Auth\UserInterface  $user
	 * @param  array  $credentials
	 * @return bool
	 */
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
