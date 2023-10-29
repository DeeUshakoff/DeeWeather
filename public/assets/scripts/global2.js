$("#setCurrentGeoLocationButton").on("click", setGeoLocationClick);


function setGeoLocationClick() {
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition((position)=> {
            sendGeoLocation(position);
            setTimeout(function(){location.reload();}, 500);
        });
    }
}