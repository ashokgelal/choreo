<x-mail::message>
Hello {{ $user->name }},
<br><br>
This is a reminder that you have the following task in progress:
<br><br>
**{{ $task->description }}**
<br><br>
No rush, just a small nudge. Take your time if you need to.

<x-mail::button :url="$url">
Visit Your Choreos
</x-mail::button>

Thanks,<br>
Team Choreo
{{ config('app.name') }}
</x-mail::message>
