<?php
/**
 * Created by PhpStorm.
 * User: Phoenix
 * Date: 12/28/2018
 * Time: 12:28 PM
 */

namespace App\Traits;

use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;

trait MailSender
{
	public static function sendEmail($to, $view, $data = [])
	{
		Mail::to($to)->send(new SendMail($view, $data));
		return Mail::failures() ? 0 : 1;
	}
	
}
