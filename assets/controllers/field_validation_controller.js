import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  connect() {
    this.element.addEventListener("focusin", (e) => {
      if (e.target.matches(".yw-input, .yw-selection-input")) {
        e.target.dataset.touched = "1"
      }
    })

    this.element.addEventListener("focusout", (e) => {
      if (e.target.matches(".yw-input, .yw-selection-input")) {
        e.target.dataset.touched = "1"
        this.validate(e.target)
      }
    })

    this.element.addEventListener("input", (e) => {
      if (e.target.matches(".yw-input")) {
        e.target.dataset.dirty = "1"
        e.target.dataset.touched = "1"

        clearTimeout(e.target._validateTimer)
        e.target._validateTimer = setTimeout(() => {
          this.validate(e.target)
        }, 300)
      }
    })

    this.element.addEventListener("change", (e) => {
      if (e.target.matches(".yw-input, .yw-selection-input")) {
        e.target.dataset.dirty = "1"
        e.target.dataset.touched = "1"
        this.validate(e.target)
      }
    })
  }

  validate(input) {
    if (this.isCheckboxGroup(input)) {
      const field = this.getGroupField(input)
      if (!field || !this.element.contains(field)) return true
      return this.validateCheckboxGroup(field, input)
    }

    if (this.isRadioGroup(input)) {
      const field = this.getGroupField(input)
      if (!field || !this.element.contains(field)) return true
      return this.validateRadioGroup(field, input)
    }

    const field = input.closest(".yw-field")
    if (!field || !this.element.contains(field)) return true

    const hasExistingError = field.querySelector(".yw-errors") !== null
    const isTouched = input.dataset.touched === "1"
    const isDirty = input.dataset.dirty === "1"
    const hasValue = (input.value ?? "").trim() !== ""

    if (!hasExistingError && !hasValue && !isTouched && !isDirty) return true

    const valid = input.checkValidity()

    input.toggleAttribute("aria-invalid", !valid)
    input.classList.toggle("yw-input--valid", valid && hasValue)

    field.classList.toggle("yw-field--invalid", !valid)
    field.classList.toggle("yw-field--valid", valid && hasValue)

    if (valid) {
      this.clearFieldErrors(field)
    }

    return valid
  }

  validateCheckboxGroup(field, input) {
    const checkboxes = this.getGroupInputs(field, input, "checkbox")
    if (!checkboxes.length) return true

    const hasExistingError = field.querySelector(".yw-errors") !== null
    const isTouched = checkboxes.some((cb) => cb.dataset.touched === "1")
    const isDirty = checkboxes.some((cb) => cb.dataset.dirty === "1")
    const checkedCount = checkboxes.filter((cb) => cb.checked).length
    const hasValue = checkedCount > 0
    const required =
      field.dataset.requiredGroup === "1" ||
      checkboxes.some((cb) => cb.required)

    if (!hasExistingError && !hasValue && !isTouched && !isDirty) return true

    const valid = !required || hasValue

    checkboxes.forEach((cb) => {
      cb.toggleAttribute("aria-invalid", !valid)
    })

    field.classList.toggle("yw-field--invalid", !valid)
    field.classList.toggle("yw-field--valid", valid && hasValue)

    if (valid) {
      this.clearFieldErrors(field)
    }

    return valid
  }

  validateRadioGroup(field, input) {
    const radios = this.getGroupInputs(field, input, "radio")
    if (!radios.length) return true

    const hasExistingError = field.querySelector(".yw-errors") !== null
    const isTouched = radios.some((radio) => radio.dataset.touched === "1")
    const isDirty = radios.some((radio) => radio.dataset.dirty === "1")
    const checkedCount = radios.filter((radio) => radio.checked).length
    const hasValue = checkedCount > 0
    const required =
      field.dataset.requiredGroup === "1" ||
      radios.some((radio) => radio.required)

    if (!hasExistingError && !hasValue && !isTouched && !isDirty) return true

    const valid = !required || hasValue

    radios.forEach((radio) => {
      radio.toggleAttribute("aria-invalid", !valid)
    })

    field.classList.toggle("yw-field--invalid", !valid)
    field.classList.toggle("yw-field--valid", valid && hasValue)

    if (valid) {
      this.clearFieldErrors(field)
    }

    return valid
  }

  getGroupField(input) {
    return (
      input.closest("fieldset.yw-field[data-required-group]") ||
      input.closest("fieldset.yw-field") ||
      input.closest(".yw-field")
    )
  }

  getGroupInputs(field, input, type) {
    return Array.from(
      field.querySelectorAll(`input[type="${type}"]`)
    ).filter((el) => el.name === input.name)
  }

  clearFieldErrors(field) {
    field.querySelectorAll(".yw-errors").forEach((el) => el.remove())
  }

  isCheckboxGroup(input) {
    return input.type === "checkbox" && (input.name || "").endsWith("[]")
  }

  isRadioGroup(input) {
    return input.type === "radio"
  }
}
