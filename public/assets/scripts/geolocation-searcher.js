

let suggestPopupContainer = $("#suggestPopupContainer")[0];
let searchLocationTextBox = $("#searchLocationTextBox")[0];
$("#searchLocationButton").on("click",function (){
    getLocationsByName(searchLocationTextBox.value)
});
$(searchLocationTextBox).on("click",function (){
    let currentVisibility = suggestPopupContainer.style.visibility;

    if(currentVisibility === 'visible'){
        suggestPopupContainer.style.visibility = 'collapse'
    }
    else {
        suggestPopupContainer.style.visibility = 'visible'
    }
});

function getLocationsByName(name) {
    $.ajax({
        url: '/GetGeoLocationByName/'+name,
        dataType: 'json',
        method: 'get',
        success: function (response) {
            suggestPopupContainer.innerHTML = '';

            response.forEach((el) => {
                let button = document.createElement('button');
                button.innerText = el.name + ', ' + el.description;
                $(button).on("click", function () {
                    let position = {
                        coords: {
                            latitude: el.latitude,
                            longitude: el.longitude
                        }
                    }
                    sendGeoLocation(position);
                    setTimeout(function(){location.reload();}, 500);

                })
                suggestPopupContainer.appendChild(button);

            })

        },
        error: function (response) {
            alert(response);
        }
    });
}

