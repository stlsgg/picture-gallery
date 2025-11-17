// Модуль, отвечающий за события формы загрузки файла
// TODO сделать переиспользуемые функции

import { initDragAndDrop, showPreview } from "./dragAndDrop.js";
import { renderState } from "../view/renderState.js";

export function form() {
  // upload form, AJAX upload
  const formElem = document.getElementById("upload-form");
  const submitBtn = document.getElementById("submit-btn");
  const toastElem = document.getElementById("toast");

  const dropZone = document.getElementById("drop-zone");
  const fileInput = dropZone.querySelector("input[type='file'][name='image']");
  const label = dropZone.querySelector("label");

  initDragAndDrop(dropZone, fileInput, label);

  // paste image event handler
  document.addEventListener("paste", (event) => {
    const items = event.clipboardData?.items;
    if (!items) return;

    for (const item of items) {
      if (item.type.startsWith("image/")) {
        const file = item.getAsFile();
        if (!file) return;

        if (file) {
          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(file);
          fileInput.files = dataTransfer.files;

          showPreview(file, dropZone);
          renderState(label, { innerText: file.name });

          return;
        }
      }
    }
  });

  submitBtn.addEventListener("click", async () => {
    const formContents = new FormData(formElem);

    const response = await fetch(formElem.action, {
      method: "POST",
      body: formContents,
    });

    const result = await response.json();
    if (result?.status === "ok") {
      toastElem.querySelector("h6").innerText = "Успешно.";
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
    } else {
      toastElem.querySelector("h6").innerText = "Ошибка.";
      toastElem.querySelector("p").innerText =
        "Не удалось загрузить картинку на сервер.";
      renderState(toastElem, { className: "toast toast-error toast-active" });
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
