<div>
  {{ __('The app, as you know, is great.') }}
  <br />
  {{ trans_choice('An app? Yes, an app!', 1) }}
  <br />
  {{ trans("Click the \":action\" button to open the app.", [
    'action' => 'Launch',
  ]) }}
</div>
