let _device_status = {};

const step_states = [
    'pending',
    'active',
    'working',
    'done'
];

function set_ui_step( step_id, state )
{
    const step = document.getElementById( step_id );
    
    if ( !step ) 
        return;

    for ( var i = 0; i < step_states.length; i++ )
    {
        step.classList.remove(step_states[i]);
    }

    step.classList.add(state);
}

async function get_status() 
{
    while (true) {
        try {
            const response = await fetch("status");

            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }

            _device_status = await response.json();

            if ( document.getElementById("sta_status") )
            {
                if (_device_status.wifi_sta_connected) {
                    document.getElementById("sta_status").innerText = "Connected";
                } else {
                    document.getElementById("sta_status").innerText = "Disconnected";
                }
            }

        } 
        catch (error) {
            console.error("Error:", error);
        }

        // wait 5 seconds
        await new Promise(resolve => setTimeout(resolve, 5000));
    }
}

document.addEventListener("DOMContentLoaded", () => 
{
    const status_bar = document.querySelector(".status_bar");

    if ( status_bar ) {
        get_status();
    }
});