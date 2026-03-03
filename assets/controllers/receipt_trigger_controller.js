import { Controller } from "@hotwired/stimulus";
import { renderStreamMessage } from "@hotwired/turbo";

export default class extends Controller {
  static targets = ["form"]
  static values = { url: String }

  update() {
    clearTimeout(this.timeout);
    this.timeout = setTimeout(() => this.fetchPreview(), 400);
  }

  async fetchPreview() {
    const formElement = this.formTarget || this.element.closest('form');
    if (!formElement) return;

    try {
      const response = await fetch(this.urlValue, {
        method: "POST",
        body: new FormData(formElement),
        headers: {
          "Accept": "text/vnd.turbo-stream.html",
          "X-Turbo-Preview": "true"
        },
      });

      if (response.ok) {
        const html = await response.text();
        renderStreamMessage(html);
      }
    } catch (error) {
      console.error("Preview update failed", error);
    }
  }

  submitEnd(event) {
    const submitter = event.detail?.formSubmission?.submitter;
    if (!submitter) return;

    const name = submitter.getAttribute("name") || "";

    if (!name.includes("[add_row]") && !name.includes("[remove_row]")) {
      return;
    }

    this.update();
  }
}
