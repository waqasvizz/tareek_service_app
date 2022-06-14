<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailLogs;

class EmailingCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \Log::info("Email Cron is working fine!");
        $posted_data = array();
        $posted_data['status'] = 1;
        $posted_data['send_email_after'] = 1;
        $posted_data['paginate'] = 5;
        $emailLogs = EmailLogs::getEmailLogs($posted_data);
        \Log::info($emailLogs);
    
        if(isset($emailLogs) && count($emailLogs)>0){
            \Log::info("Inside Loop, Email sending is in process now!");
            foreach ($emailLogs as $key => $value)
            {
                $data = [
                    'body' => $value->email_message,
                    'subject' => $value->subject,
                    'email' => $value->email
                ];
    
                Mail::send('emails.email_template', $data, function($message) use ($data) {
                    $message->to($data['email'])
                    ->subject($data['subject']);
                });
    
                $posted_data = array();
                $posted_data['update_id'] = $value->id;
                if (Mail::failures()) {
                    $posted_data['status'] = 4;
                    $posted_data['status_message'] = 'Email sending failed.';
                }
                else {
                    $posted_data['status'] = 2;
                    $posted_data['send_at'] = date('Y-m-d h:i:s');
                    $posted_data['status_message'] = 'Email sent successfully.';
                }
                EmailLogs::saveUpdateEmailLogs($posted_data);
            }
        }else{
            \Log::info("Don't have email for sending.");
        }
    }
}
