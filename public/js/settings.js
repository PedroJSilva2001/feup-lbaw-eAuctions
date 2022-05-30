function addEventListeners() {
    const btn = document.querySelector('#transfer-btn');
    if (btn) {
        btn.addEventListener('click', () => {
            $sum = document.querySelector('#amount').value;
            document.querySelector('#transfer-sum').innerHTML = "Sum:  " + $sum + " €";
        });
    }
}

addEventListeners();