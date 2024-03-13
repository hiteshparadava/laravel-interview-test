<?php

use App\Models\Prize;

$current_probability = floatval(Prize::sum('probability'));
$remain_probability = 100-$current_probability;
?>
@if($remain_probability != 0)
<div class="alert alert-danger">Sum of all Prizes probability must be 100 % Currently it is {{$current_probability}}% you have yet to add {{$remain_probability}}% to the Prizes</div>
@endif