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
    public function sendEmailToUser($email, $data, $bookname, $get_BookAuthor, $Quantity, $Total_Price,$adminEmail)
    {
        $name = 'BookStore';
        $email = $email;
        $subject = 'Your Order Summary';
        $data = " Hello Chandan Kumar!!!!<br><br>Yo have Placed an Order From BookStore App <br><br> Hurray!!!!your order is confirmed .<br><br> Your Order Summary is : <br><br>" . "Order_Id : " . $data . "<br><br>Book Name : " . $bookname . "<br><br>Book Author : " . $get_BookAuthor . "<br><br>Book Quantity : " . $Quantity . "<br><br>Total Payment : " . $Total_Price ."<br><br>Save the OrderId For Further Communication"."<br>For Further Querry Contact This Email Id <br>".$adminEmail ;

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
