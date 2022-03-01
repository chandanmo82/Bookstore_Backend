<?php

namespace App\Http\Requests;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @since 22-Feb-2022
 * 
 * This class is respnsible for sending the message to the given email id and token.
 */
class SendEmailRequest
{

    /**
     * @param $email,$token
     * 
     * This function takes two args from the function in ForgotPasswordcontroller and successfully 
     * sends the token as a reset link to the user email id. 
     */
    public function sendEmail($email, $token)
    {
        $name = 'Chandan Kumar';
        $email = $email;
        $subject = 'Regarding your Password Reset';
        $data = "Hi Chandan Kumar <br> Your password Reset Link is <br>" . $token;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->setFrom(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject =  $subject;
            $mail->Body    = $data;
            $dt = $mail->send();
            sleep(3);

            if ($dt)
                return true;
            else
                return false;
        } catch (Exception $e) {
            return back()->with('error', 'Message could not be sent.');
        }
    }
    public function sendEmailToUser($email, $data, $bookname, $get_BookAuthor, $Quantity, $Total_Price)
    {
        $name = 'BookStore';
        $email = $email;
        $subject = 'Your Order Summary';
        $data = "Hurray!!!!your order is confirmed and the order summary is : <br>" . "Order_Id : " . $data . "<br>Book Name : " . $bookname . "<br>Book Author : " . $get_BookAuthor . "<br>Book Quantity : " . $Quantity . "<br>Total Payment : " . $Total_Price ."<br>Save the OrderId For Further Communication";

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->setFrom(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject =  $subject;
            $mail->Body    = $data;
            $dt = $mail->send();

            if ($dt) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return back()->with('error', 'Message could not be sent.');
        }
    }
}
