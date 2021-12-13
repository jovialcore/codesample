
<?php 
use App\Jobs\ScheduleOrSendMail;
use App\Mail\Email;
use App\Models\Contact;
use App\Models\messages;
class EmailApiController extends Controller
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail as FacadesMail;
{
public function store(Request $request)
    {

        $msg = new messages(); // there is a reason while I actually instanciated the message class above the if statement so that i can able to chain the methods (esp $msg-save(), etc) without having if statement scope issues or interferance

        if ($request->has('email') && $request->has('message') && $request->has('title')) {

            $details = [
                'title' => $request->title,
                'message' => $request->message
            ];

            $msg->message = $request->message;
            $msg->title = $request->title;
            $msg->user_id = 1; //ideal method not working because of api authentication issues/// should actually be fixed with a package like passport or so
            $msg->save();
            //collecting the ids from the mail table esp as an array of integers
            foreach ($request->email as $email) {
                $id = Contact::where('email', $email)->first();
                $ContactIds[] = $id->id;
            }
            $msg->contacts()->syncWithoutDetaching($ContactIds); // behind the scene, this code does  insert into `contacts_messages` (`contact_id`, `messages_id`) values (5, 1)


            if ($request->when == 'not now') //make sense ...clean code {
            {
                foreach ($request->email as $key => $mails) {

                    $name = Contact::where('email', $mails)->first()->name;

                    dispatch(new ScheduleOrSendMail($mails, new Email($details, $name)));
                }

                return response()->json(['success' => 'Hurray..Mail was successfully sent']);
            } else {

                $scheduleDate =  date('Y-m-d H:i', strtotime($request->scheduleTime));
                $msg->schedule_time = $scheduleDate;
                $msg->save();
                return response()->json(['success' => 'Mail will be sent at this date', 'dateOfDelivery' => $scheduleDate]);
            }
        }
    }
}
