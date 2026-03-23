import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = ["container", "row", "title", "prototype"]
  static values = {
    rowLabel: String,
    removeLabel: String,
    targetName: String
  }

  connect() {
    if (this.rowTargets.length === 0) {
      this.addRow()
    } else {
      this.refreshTitles()
    }
  }

  addRow() {
    if (!this.hasPrototypeTarget) return

    const index = this.nextIndex()
    const html = this.prototypeTarget.innerHTML.replaceAll("__name__", String(index))

    this.containerTarget.insertAdjacentHTML("beforeend", html)
    this.refreshTitles()

    const rows = this.rowTargets
    const newRow = rows[rows.length - 1]

    if (newRow) {
      this.clearRowValues(newRow)
      this.clearRowErrors(newRow)
      this.refreshTitles()
    }
  }

  removeRow(event) {
    const row = event.currentTarget.closest("[data-yw-repeatable-target='row']")
    if (!row) return

    if (this.rowTargets.length <= 1) {
      this.clearRowValues(row)
      this.clearRowErrors(row)
      this.refreshTitles()
      return
    }

    row.remove()
    this.refreshTitles()
  }

  nextIndex() {
    let max = -1

    this.containerTarget.querySelectorAll("[name]").forEach((el) => {
      const match = el.name.match(/\[(\d+)\](?=\[[^\]]+\]$)/)
      if (match) {
        max = Math.max(max, Number(match[1]))
      }
    })

    return max + 1
  }

  clearRowValues(row) {
    row.querySelectorAll("input, select, textarea").forEach((el) => {
      if (el.type === "checkbox" || el.type === "radio") {
        el.checked = false
      } else if (el.tagName === "SELECT") {
        el.selectedIndex = 0
      } else {
        el.value = ""
      }

      el.removeAttribute("aria-invalid")
      el.classList.remove("yw-input--valid")
      delete el.dataset.dirty
      delete el.dataset.touched
    })

    row.querySelectorAll(".yw-field").forEach((field) => {
      field.classList.remove("yw-field--invalid", "yw-field--valid")
    })
  }

  clearRowErrors(row) {
    row.querySelectorAll(".yw-errors").forEach((el) => el.remove())
  }

  refreshTitles() {
    this.rowTargets.forEach((row, index) => {
      const title = row.querySelector("[data-yw-repeatable-target='title']")
      if (!title) return

      title.textContent = `${this.rowLabelValue} ${index + 1}`
    })
  }
}
