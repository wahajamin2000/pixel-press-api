<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class NotificationApiController extends Controller
{

    public function notifications(Request $request)
    {
        $notifications = Notification::where('user_id','=',auth()->user()->id)->orderby('id', 'DESC')->get();

        return $this->response(Response::HTTP_OK, __('Fetched Successfully'), [
            'notifications' => NotificationResource::collection($notifications),
        ]);
    }

    public function show($notification)
    {
        $notifications = Notification::where('id',$notification)->first();

        if( !isset($notifications) || $notifications == '' || $notifications == null){
            return $this->response(Response::HTTP_OK, __('No Notification Found'), []);
        }

        return $this->response(Response::HTTP_OK, __('Fetched Successfully'), [
            'notification' => new NotificationResource($notifications),
        ]);
    }

    public function deleteNotification($notification)
    {
        $notifications = Notification::where('id',$notification)->first();

        if( !isset($notifications) || $notifications == '' || $notifications == null || $notifications->user_id != auth()->user()->id){
            return $this->response(Response::HTTP_OK, __('No Notification Found'), []);
        }

        $notifications->forceDelete();

        return $this->response(Response::HTTP_OK, __('Notification Deleted Successfully!'), []);
    }

    public function deleteAllNotification()
    {
        $notifications = isset(auth()->user()->notifications) ? auth()->user()->notifications : '';

        if( !isset($notifications) || $notifications == '' || $notifications == null){
            return $this->response(Response::HTTP_OK, __('No Notification Found'), []);
        }

        foreach ($notifications as $notification){
            $notification->forceDelete();
        }

        return $this->response(Response::HTTP_OK, __('All Notifications Deleted Successfully!'), []);
    }

    public function readNotification($notification)
    {
        $notifications = Notification::where('id',$notification)->first();

        if( !isset($notifications) || $notifications == '' || $notifications == null || $notifications->user_id != auth()->user()->id){
            return $this->response(Response::HTTP_OK, __('No Notification Found'), []);
        }

        $notifications->is_read = 1;
        $notifications->save();

        return $this->response(Response::HTTP_OK, __('Notification Read Successfully!'), []);
    }

    public function readAllNotification()
    {
        $notifications = isset(auth()->user()->notifications) ? auth()->user()->notifications : '';

        if( !isset($notifications) || $notifications == '' || $notifications == null){
            return $this->response(Response::HTTP_OK, __('No Notification Found'), []);
        }

        foreach ($notifications as $notification){
            $notification->is_read = 1;
            $notification->save();
        }

        return $this->response(Response::HTTP_OK, __('All Notifications Read Successfully!'), []);
    }

    public function notificationsCount()
    {
        $user = auth()->user();
        $notifications = Notification::where('user_id',$user->id)->where('is_read','=','0')->get();

        if( !isset($notifications) || $notifications == '' || $notifications == null){
            return $this->response(Response::HTTP_OK, __('No Notifications Found'), []);
        }

        $notifications = count($notifications);

        return $this->response(Response::HTTP_OK, __('Fetched Successfully'), [
            'count' => $notifications,
        ]);
    }

    public function status(Request $request)
    {
        $notification = $request->is_notification;
        $user = auth()->user();
        $user->is_notification = $notification;
        $user->save();

        return $this->response(Response::HTTP_OK, __('Notification Status Updated Successfully!'), [
            'notification' => (int)$notification,
        ]);
    }

    public function notificationStatus(Request $request)
    {
        $user = auth()->user();
        $notification = $user->is_notification ?? 0;

        return $this->response(Response::HTTP_OK, __('Fetched Successfully!'), [
            'notification' => (int)$notification,
        ]);
    }

}
