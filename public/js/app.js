function encodeForAjax(data) {
  if (data == null) return null;
  return Object.keys(data).map(function(k){
    return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
  }).join('&');
}

function sendAjaxRequest(method, url, data, handler) {
  let request = new XMLHttpRequest();

  request.open(method, url, true);
  request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
  request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  request.addEventListener('load', handler);
  request.send(encodeForAjax(data));
}


// -------------------------

function preventHref() {
  const elements = document.querySelectorAll(".same-page");
  
  for (el of elements) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
    });
  }
}

preventHref();

// Login: show password
function showPassword() {
  const b = document.querySelector('#password-login'); 
  const checkbox = document.querySelector('.show-pass'); 
  b.type = (checkbox.checked) ? 'text' : 'password';
}

// Register: show password
function showPasswordRegister() {
  const btn = document.querySelector('#password'); 
  const btnConfirm = document.querySelector('#password-confirm'); 
  const checkbox = document.querySelector('.show-pass'); 
  btn.type = (checkbox.checked) ? 'text' : 'password';
  btnConfirm.type = (checkbox.checked) ? 'text' : 'password';
}

function addEventListeners() {
  // Services
  const services_user = document.querySelector('#services-user');
  if (services_user) {
    services_user.addEventListener('click', () => {
        document.querySelector('#list-user').style.display = 'block';
        document.querySelector('#list-auctions').style.display = 'none';
        document.querySelector('#list-admins').style.display = 'none';
    });
  }

  const services_auctions = document.querySelector('#services-auctions');
  if (services_auctions) {
    services_auctions.addEventListener('click', () => {
        document.querySelector('#list-user').style.display = 'none';
        document.querySelector('#list-auctions').style.display = 'block';
        document.querySelector('#list-admins').style.display = 'none';
    });
  }

  const services_admins = document.querySelector('#services-admins');
  if (services_admins) {
    services_admins.addEventListener('click', () => {
        document.querySelector('#list-user').style.display = 'none';
        document.querySelector('#list-auctions').style.display = 'none';
        document.querySelector('#list-admins').style.display = 'block';
    });
  }
}

addEventListeners();
