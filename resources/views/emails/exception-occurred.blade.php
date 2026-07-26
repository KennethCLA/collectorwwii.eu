<p><strong>Exception:</strong> {{ $exceptionClass }}</p>
<p><strong>Message:</strong> {{ $exceptionMessage }}</p>
<p><strong>URL:</strong> {{ $url }}</p>
<p><strong>Location:</strong> {{ $file }}:{{ $line }}</p>
<p><strong>Occurred at:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
<p><strong>Trace:</strong></p>
<pre style="white-space: pre-wrap; font-size: 12px;">{{ $trace }}</pre>
