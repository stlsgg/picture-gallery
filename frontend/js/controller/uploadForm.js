// Модуль, отвечающий за события формы загрузки файла
// TODO сделать переиспользуемые функции

import { initDragAndDrop } from "./dragAndDrop.js";

export function form() {
  // upload form, AJAX upload
  const formElem = document.getElementById("upload-form");
  const submitBtn = document.getElementById("submit-btn");
  const toastElem = document.getElementById("toast");

  const dropZone = document.getElementById("drop-zone");
  const fileInput = dropZone.querySelector("input[type='file'][name='image']");
  const label = dropZone.querySelector("label");

  initDragAndDrop(dropZone, fileInput, label);

  submitBtn.addEventListener("click", async () => {
    const formContents = new FormData(formElem);

    const response = await fetch(formElem.action, {
      method: "POST",
      body: formContents,
    });

    const result = await response.json();
    if (result?.status === "ok") {
      toastElem.querySelector("h6").innerText = "Успешно!";
      toastElem.querySelector("p").innerText =
        "Картинка была загружена на сервер.";
      renderState(toastElem, { className: "toast toast-success toast-active" });
      toastElem
        .querySelector("i.icon.icon-cross")
        .addEventListener("click", () => {
          toastElem.querySelector("h6").innerText = "";
          toastElem.querySelector("p").innerText = "";
          renderState(toastElem, { className: "toast" });
        });
    }
    formElem.reset();
    label.innerText = "Перетащите сюда картинку :3";
    console.log(result);
    window.location.href = "#close";
  });
}
