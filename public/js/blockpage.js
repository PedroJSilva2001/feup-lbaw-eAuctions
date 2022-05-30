
document.querySelector('#block_user_btn').addEventListener('click', () => {
    const cb = document.querySelector('#block_cb');
    const datePicker = document.querySelector('#end_date');

    if (cb.checked) {
        datePicker.valueAsDate = null;
        datePicker.value = null;
    }
});


document.querySelector('#block_cb').addEventListener('click', () => {
    const datePicker = document.querySelector('#end_date');
    const cb = document.querySelector('#block_cb');

    if (cb.checked) {
        datePicker.removeAttribute('required');
        datePicker.valueAsDate = null;
        datePicker.value = null;
        datePicker.disabled = true;
    } else {
        datePicker.setAttribute('required','required')
        datePicker.disabled = false;
    }
});