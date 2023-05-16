<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class NewTaskAssign extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->task->assignedBy->name.' assigned you a new task')
                    ->line('Hello '.$notifiable->name)
                    ->line($this->task->assignedBy->name.' assigned you a new task')
                    ->line($this->task->name)
                    ->action('View Task', route('tasks.show', $this->task->id))
                    ->line('Thank you for using our application!');


    }


    public function toSlack($notifiable)
    {
        return (new SlackMessage)
                    ->from('Ghost', ':ghost:')
                    ->to('#task_notifications')
                    ->success()
                    ->content('Hey there :smile: '.$this->task->assignedBy->name.' assigned you a new task '.$this->task->name)
                    ->attachment(function ($attachment) {
                        $attachment->title('Task Details', route('tasks.show', $this->task->id))
                                    ->fields([
                                        'Task Name' => $this->task->name,
                                        'Task Description' => $this->task->description,
                                        'Assigned By' => $this->task->assignedBy->name,
                                        'Assigned To' => $this->task->users->pluck('name')->implode(', '),
                                    ]);
                        $attachment->footer('KaykeWalk')
                                    ->timestamp($this->task->created_at);
                    });
        
                    
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            
        ];
    }

    // public function routeNotificationForMail($notification)
    // {
    //     return $this->task->users->pluck('email')->toArray();
    // }


}
