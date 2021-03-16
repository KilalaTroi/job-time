Project: {{$p_name}} <br> 
Issue: {{$i_name}} <br>
Info: {{$phase}} <br>
Type: {{$type}} <br> 
Data: {{$data}} <br> 
Inkjet: {{$inkjet}} <br> 
Finish RQ: {{$finish_rq}} <br> 
Reporter: {{$user['name']}} <br>
Status: {{$status}} <br>
@if ( $page && $status == 'Finished Work' )
Page Work: {{$page}} <br>
@endif
@if ( $file && $status == 'Finished Work' )
File Work: {{$file}} <br>
@endif
<br>
Note:  <br>{!! $content !!} 