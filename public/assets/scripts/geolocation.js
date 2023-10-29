const x = document.getElementById("location");

// function getLocation() {
//     if (navigator.geolocation) {
//         navigator.geolocation.getCurrentPosition(sendGeoLocation);
//
//     } else {
//         x.innerHTML = "Geolocation is not supported by this browser.";
//     }
// }

function getGeoLocationFromSession(){
    $.ajax({
        url: '/GetGeoLocationFromSession',
        method: 'get',
        dataType: 'html',
        success: function(response){
            let responseLongitude = response.longitude;
            let responseLatitude = response.latitude;

            if(navigator.geolocation){
                navigator.geolocation.getCurrentPosition((position)=>function () {
                    if(position.latitude != responseLatitude || position.longitude != responseLongitude){
                        alert("Геолокация изменилась");
                    }
                });
            }
            else {
                alert("Необходимо разрешение на отправку Геоданных");
            }


            //window.location.href='/';
        },
        error: function (response){
            alert(response);
        }
    });
}
function sendGeoLocation(position) {
    // x.innerHTML = "Latitude: " + position.coords.latitude +
    //     "<br>Longitude: " + position.coords.longitude;

    $.ajax({
        url: '/SetGeoLocation',
        method: 'post',
        dataType: "json",
        data: {latitude: position.coords.latitude, longitude: position.coords.longitude},
        success: function(data){
            console.log(data)
        },
        error: function (data){
            alert(data);
        }
    });
}