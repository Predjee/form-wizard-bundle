import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static values = {
    url: String
  }

  connect() {
    this.element.addEventListener('focusout', (e) => {
      if (e.target.matches('.yw-input')) {
        this.validate(e.target)
      }
    })

    this.element.addEventListener('input', (e) => {
      if (e.target.matches('.yw-input')) {
        clearTimeout(e.target._validateTimer)
        e.target._validateTimer = setTimeout(() => {
          this.validate(e.target)
        }, 400)
      }
    })

    this.element.addEventListener('change', (e) => {
      if (e.target.matches('.yw-input')) {
        this.validate(e.target)
      }
    })
  }

  async validate(input) {
    const field = input.closest('.yw-field')
    if (!field) return

    const hasExistingError = field.querySelector('.yw-errors') !== null
    const hasValue = input.value.trim() !== ''

    if (!hasExistingError && !hasValue) return

    const body = new FormData()
    body.append('field_id', input.id)
    body.append('field_name', input.name.match(/\[([^\]]+)\]$/)?.[1] ?? input.name)
    body.append('field_value', input.value)

    const form = input.closest('form')
    if (form) {
      new FormData(form).forEach((value, key) => {
        body.append(key, value)
      })
    }

    const response = await fetch(this.urlValue, {
      method: 'POST',
      headers: {
        'Accept': 'text/vnd.turbo-stream.html',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body,
    })

    const html = await response.text()
    const isValid = html.includes('action="remove"')

    if (isValid) {
      field.querySelector('.yw-errors')?.remove()
      input.removeAttribute('aria-invalid')
      if (input.value.trim() !== '') {
        input.classList.add('yw-input--valid')
      }
    } else {
      input.classList.remove('yw-input--valid')
      input.setAttribute('aria-invalid', 'true')
    }
  }
}
