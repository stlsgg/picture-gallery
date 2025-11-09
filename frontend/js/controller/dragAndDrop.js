// реализация drag and drop

// TODO избавиться от прямого импорта
// NOTE прочитать про Dispatch Events, аналог EventEmmiter
// NOTE пока что renderState только усложняет логику в этом модуле. Я хотел
// обновить стейт для dropZone, но я потеряю другие классы dropZone если бы
// вызвал renderState(dropZone, { className: "active" })
import { renderState } from "../view/renderState.js";

/**
 * Initialize drag-and-drop behavior for file upload with live image preview.
 *
 * Handles drag-and-drop and manual file selection events, provides visual
 * feedback, validates input files, and displays an image preview inside the
 * drop zone.
 *
 * @function initDragAndDrop
 * @param {HTMLElement} dropZone - The drag-and-drop target element.
 * @param {HTMLInputElement} fileInput - The hidden file input element.
 * @param {HTMLElement} label - The label element showing the file name.
 *
 * @description
 * - Adds/removes the `active` class on drag events.
 * - Accepts only one file and only image MIME types (`image/*`).
 * - Displays a live image preview inside the drop zone.
 * - Updates the label text with the file name.
 * - Validates both dropped and manually selected files.
 *
 * @todo Replace `alert()` calls with proper UX feedback (e.g., toast or inline error message).
 * @todo Consider moving validation and preview rendering to separate helper functions.
 */
export function initDragAndDrop(dropZone, fileInput, label) {
  dropZone.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropZone.classList.add("active");
  });

  dropZone.addEventListener("dragleave", (e) => {
    e.preventDefault();
    dropZone.classList.remove("active");
  });

  dropZone.addEventListener("drop", (e) => {
    e.preventDefault();
    dropZone.classList.remove("active");

    const files = e.dataTransfer.files;
    if (files.length !== 1) {
      // TODO UX реакция на ошибку
      alert("можно прикрепить только один файл");
      return;
    }

    const file = files[0];
    if (!file.type.startsWith("image/")) {
      // TODO UX реакция на ошибку
      alert("можно прикрепить только изображения");
      return;
    }

    fileInput.files = files;
    showPreview(file, dropZone);
    renderState(label, { innerText: file.name });
  });

  fileInput.addEventListener("change", () => {
    const files = fileInput.files;

    if (files.length !== 1) {
      // TODO UX реакция на ошибку
      alert("можно прикрепить только один файл");
      return;
    }

    const file = files[0];
    if (!file.type.startsWith("image/")) {
      // TODO UX реакция на ошибку
      alert("можно прикрепить только изображения");
      return;
    }

    fileInput.files = files;

    showPreview(file, dropZone);
    renderState(label, { innerText: file.name });
  });
}

export function showPreview(file, dropZone) {
  dropZone.querySelectorAll("img").forEach((child) => {
    dropZone.removeChild(child);
  });
  const imgPreview = document.createElement("img");
  imgPreview.className = "preview";
  const url = URL.createObjectURL(file);
  imgPreview.src = url;
  imgPreview.onload = () => URL.revokeObjectURL(url);
  dropZone.appendChild(imgPreview);
}
