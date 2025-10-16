@php($title = "Text here base")

<div>
  {{ trans($title) }}
  <br />
  {{ trans($test . 'base') }}
  <br />
  {{ trans("Base test [example]") }}
  <br />
  {{ __("The app's performance can't be beat.") }}
  <br />
  {{ trans("Don't forget to update your app.") }}
  <br />
  {{ trans("If you're enjoying this app, please leave a review!") }}
</div>
