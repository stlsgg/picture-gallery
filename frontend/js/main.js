// include modules here
import { API_URL, DOM_IDS } from "./model/constants.js";
import { getDOMElements } from "./view/domElements.js";
import { checkAPI, fetchDB, fetchImages } from "./controller/api.js";
import {
  getFirstElement,
  getLastElement,
  getTotalPages,
} from "./controller/pagination.js";
import { form } from "./controller/uploadForm.js";
import { renderState } from "./view/renderState.js";

// загружаю constants
// DE - DOM Elements
const DE = getDOMElements(DOM_IDS);

async function loadPage(pageNum, itemsOnPage = 10) {
  // вычисляю пул картинок на страницу
  let firstEl = getFirstElement(pageNum, itemsOnPage);
  let lastEl = getLastElement(pageNum, itemsOnPage);
  renderState(DE["gallery"], {
    innerHTML: "<div class='loading loading-lg' id='loading-state'></div>",
    className: "gallery gallery-single",
  });

  // загружаю картинки из сервера
  // быстрый чек доступности
  if (!checkAPI(API_URL)) console.error("api not working!");
  const images = await fetchImages(firstEl, lastEl, API_URL);

  images.forEach((image, idx) => {
    // загружаю на страницу
    const card = document.createElement("div");
    card.innerHTML = createCard(image?.thumb, image?.desc || "без описания!");

    card.addEventListener("click", () => {
      const modalFullImageElem = document.getElementById(
        "modal-full-image-window",
      );

      const loading = document.createElement("div");
      loading.className = "loading loading-lg";
      const modalFullImage = modalFullImageElem.querySelector("img");
      modalFullImage.replaceWith(loading);

      const newImage = document.createElement("img");
      newImage.className = "img-responsive";
      const desc = modalFullImageElem.querySelector(".description");
      newImage.src = `${API_URL}/${images[idx]?.full}`;
      newImage.alt = images[idx]?.desc || "нет описания, пипяу ;D";
      desc.textContent = images[idx]?.desc || "нет описания, пипяу ;D";

      loading.replaceWith(newImage);
      window.location.href = "#modal-full-image-window";
    });

    renderState(DE["gallery"], {
      className: "gallery",
    });

    DE["gallery"].appendChild(card);
  });
  DE["gallery"].removeChild(document.getElementById("loading-state"));
}

function createCard(src, desc) {
  return `<div class="card"><div class="card-image"> <img src="${API_URL}/${src}"
alt="${desc}" class="img-responsive" > </div> <div class="card-body">
${desc} </div></div>`;
}

form();
loadPage(1);

async function initPagination() {
  const imageDB = await fetchDB(API_URL);
  const totalPages = getTotalPages(imageDB.length, 10);

  const paginationContainer = document.getElementById("pagination");
  renderState(paginationContainer, { innerHTML: "" });

  for (let i = 1; i <= totalPages; i++) {
    const pageItem = document.createElement("li");
    // Yoda conditions
    const isFirstPage = 1 === i;
    renderState(pageItem, {
      className: isFirstPage ? "c-hand page-item active" : "c-hand page-item",
    });

    const pageAnchor = document.createElement("a");
    renderState(pageAnchor, { innerText: i });

    pageAnchor.addEventListener("click", () => {
      const paginationItems = paginationContainer.querySelectorAll("li");
      paginationItems.forEach((element) =>
        renderState(element, { className: "c-hand page-item" }),
      );
      renderState(pageItem, { className: "c-hand page-item active" });
      loadPage(i);
    });

    pageItem.appendChild(pageAnchor);
    paginationContainer.appendChild(pageItem);
  }
}
