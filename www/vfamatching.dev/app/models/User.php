<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {
	protected $guarded = array();

	public static $createRules = array(
        'email'=> 'email|unique';
        'lastLogin'=>'date';
        'role'=>'in:Admin, Fellow, Hiring Manager';
        'firstName'=>'max:100';
        'lastName'=>'max:100';
        );
    public static $updateRules = array(
        'email'=> 'email';
        'lastLogin'=>'date';
        'role'=>'in:Admin, Fellow, Hiring Manager';
        'firstName'=>'max:100';
        'lastName'=>'max:100';
        );

	 /* Required for Laravel Auth */
    protected $hidden = array('password');

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }

	public function profile()
	{
		if($this->role == 'Fellow') {
			return $this->hasOne('Fellow');
		} elseif($this->role == 'Hiring Manager') {
			return $this->hasOne('HiringManager');
		} elseif($this->role == 'Admin') {
			return $this->hasOne('Admin');
		} else {
			throw new Exception("Invalid User role!");
		}
	}
}
