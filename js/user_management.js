import{
  submitForm
} from "@tsjippy/form_submit_functions";

import { 
  displayMessage 
} from "@tsjippy/display_message";

async function disableUserAccount(target) {
  var response = await submitForm(
    target,
    "user_management/disable-user-account",
  );

  if (response) {
    if (target.textContent.includes("Disable")) {
      target.textContent = target.textContent.replace("Disable", "Enable");
    } else {
      target.textContent = target.textContent.replace("Enable", "Disable");
    }
    displayMessage(response);
  }
}

async function updateUserRoles(target) {
  var response = await submitForm(
    target,
    "user_management/update_roles",
  );

  if (response) {
    displayMessage(response);
  }
}

async function extendValidity(target) {
  var response = await submitForm(
    target,
    "user_management/extend_validity",
  );

  if (response) {
    displayMessage(response);
  }
}

async function createUserAccount(target) {
  var response = await submitForm(
    target,
    "user_management/add_useraccount",
  );

  if (response) {
    displayMessage(response.message);
  }
}

document.addEventListener("click", (ev) => {
  const target = ev.target;

  if (target.name == "disable-user-account") {
    ev.preventDefault();
    disableUserAccount(target);
  } else if (target.name == "updateroles") {
    ev.preventDefault();
    updateUserRoles(target);
  } else if (target.name == "extend_validity") {
    ev.preventDefault();
    extendValidity(target);
  } else if (target.name == "adduseraccount") {
    ev.preventDefault();
    createUserAccount(target);
  } else {
    return;
  }

  ev.stopImmediatePropagation();
});

console.log("user management js loaded");
