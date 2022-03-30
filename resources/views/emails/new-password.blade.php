@component('mail::message')
# فراموشی کلمه عبور

کلمه عبور جدید شما: 
{{ $newPass }}

{{ config('app.name') }}
@endcomponent