Project: {{$p_name}} <br> 
@if ( $page && $status == 'Finished Work' )
Page Work: {{$page}} <br>
@endif
Issue: {{$i_name}} <br>
Info: {{$phase}} <br>
Reporter: {{$user['name']}} <br>
Status: {{$status}} <br>
Description: {{$content}} 