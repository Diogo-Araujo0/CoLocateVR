<html>
    <head>
       
    </head>
    <body>
    <h1>Devices</h1>
    @foreach($devices as $device)
    <h4> ID: {{ $device->id }}</h4>
    <h4> Serial Number: {{ $device->serialNumber }}</h4>
    -----------------------
    @endforeach
    <h1>Players</h1>
    @foreach($players as $player)
    <h4> ID: {{ $player->id }}</h4>
    <h4> Phone Number: {{ $player->phoneNumber }}</h4>
    -----------------------
    @endforeach


    <h1>Groups</h1>
    @foreach($groups as $group)
    <h4> ID: {{ $group->id }}</h4>
    -----------------------
    @endforeach
    
@php 
$lastGroup = null;
@endphp
    
    @foreach($groupsInSession as $groupsInSessions)
        @if($lastGroup != $groupsInSessions->group_id)
            <h1>Group {{ $groupsInSessions->group_id }}</h1>
            <h2>Players:</h2>
            @php
            $lastGroup = $groupsInSessions->group_id
            @endphp
            
        @endif
        <h4> ID: {{ $groupsInSessions->player_id }}</h4>
        <h4> PhoneNumber: {{ $groupsInSessions->phoneNumber }}</h4>
        <h4> Device: {{ $groupsInSessions->serialNumber }}</h4>
        -----------------------
    @endforeach
    
    <h1>Devices Available</h1>
    @foreach($devicesAvailable as $devices)
        Device: {{ $devices->id }}
    @endforeach


    
    
@if($sessionInfo)
    <h2>Session Info</h2>
    <p>Start Time: {{ $sessionInfo->start_time }}</p>
    <p>End Time: {{ $sessionInfo->end_time }}</p>
    
    @if(isset($timeLeft))
        <p>Time Left: {{ $timeLeft }}</p>
    @else
        <?php
        $endTime = new DateTime($sessionInfo->end_time);
        $currentTime = new DateTime();
        $interval = $currentTime->diff($endTime);
        $timeLeft = $interval->format('%H:%I:%S');
        ?>
        <p>Time Left: {{ $timeLeft }}</p>
    @endif
@endif














    
    </body>
</html>