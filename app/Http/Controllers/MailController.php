<?php

namespace App\Http\Controllers;

use App\Mail\AcualMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendEmail($body,$emailTo){
        $details = [
            'title' => 'Mail from E-commerce application',
            'body' => $body,
        ];
        Mail::to($emailTo)->send(new AcualMail($details));
        return "email sent";
    }
}
