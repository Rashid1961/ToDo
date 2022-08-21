<?php 

namespace App\Http\Controllers; 
use Illuminate\Http\Request; 
use App\Models\Contact; 

class ContactController extends Controller { 

     public function create() { 

      return view('contact_form'); 
     }  

     public function store(Request $request) { 
      $contact = new Contact;

      $contact->name = $request->name;
      $contact->email = $request->email;
      $contact->subject = $request->subject;
      $contact->mobile_number = $request->mobile_number;
      $contact->message = $request->message;

      $contact->save();
      
      return response()->json(['success'=>'Form is successfully submitted!']);

    }
}