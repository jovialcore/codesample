
<?php 
namespace App\Http\Controllers\Api;
use App\Jobs\ScheduleOrSendMail;
use App\Mail\Email;
use App\Models\Contact;
use App\Models\messages;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail as FacadesMail;


class EmailApiController extends Controller {

public function store(Request $request)
    {

        $msg = new messages(); // there is a reason while I actually instanciated the message class above the if statement so that i can able to chain the methods (esp $msg-save(), etc) without having to get "if-statement" scope issues or interferance

        if ($request->has('email') && $request->has('message') && $request->has('title')) {

            $details = [
                'title' => $request->title,
                'message' => $request->message
            ];
         // get the details from request body
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

// check if value for the request payload "when"  is "not now", if it is , then we loop through the emails from the request body, also get their names then we pass  the ...
// details to the dispacth job function that will send the mail immediately following a json response of "success" (mail was successfully sent"
            if ($request->when == 'not now') {
            {
                foreach ($request->email as $key => $mails) {

                    $name = Contact::where('email', $mails)->first()->name;

                    dispatch(new ScheduleOrSendMail($mails, new Email($details, $name)));
                }

                return response()->json(['success' => 'Hurray..Mail was successfully sent']);
            } else {
// here, for the else, if the  request payload "when" value  is not "not now",  we set a schedule date for it, set the date in the time format we want and save it to the db , then tell the user
 // via a json response that the message will be set on that paticurlar date entered in the frontend 
                $scheduleDate =  date('Y-m-d H:i', strtotime($request->scheduleTime));
                $msg->schedule_time = $scheduleDate;
                $msg->save();
                return response()->json(['success' => 'Mail will be sent at this date', 'dateOfDelivery' => $scheduleDate]);
            }
        }
    }
}
