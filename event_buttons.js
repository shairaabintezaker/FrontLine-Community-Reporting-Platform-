function respondEvent(eventId, type) {
    fetch('event_response.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'event_id=' + eventId + '&response_type=' + type
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => console.error(err));
}