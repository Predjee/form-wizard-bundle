import {AbstractFormToolbarAction} from 'sulu-admin-bundle/views';
import snackbarStore from 'sulu-admin-bundle/stores/snackbarStore';
import {translate} from 'sulu-admin-bundle/utils';
import {extendObservable, action} from 'mobx';

const parseFilenameFromContentDisposition = (value, fallback) => {
  if (!value) return fallback;

  const star = value.match(/filename\*\s*=\s*UTF-8''([^;]+)/i);
  if (star && star[1]) {
    try {
      return decodeURIComponent(star[1].replace(/(^"|"$)/g, ''));
    } catch (e) {}
  }

  const normal = value.match(/filename\s*=\s*("?)([^";]+)\1/i);
  if (normal && normal[2]) return normal[2];

  return fallback;
};

const triggerBrowserDownload = (blob, filename) => {
  const url = window.URL.createObjectURL(blob);
  try {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.rel = 'noopener';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } finally {
    window.URL.revokeObjectURL(url);
  }
};

export default class ExportToolbarAction extends AbstractFormToolbarAction {
  constructor(...args) {
    super(...args);

    extendObservable(this, {
      loading: false,
    });

    this.setLoading = action((state) => {
      this.loading = state;
    });

    this.handleClick = () => {
      const id = this.resourceFormStore?.id;

      if (!id) {
        snackbarStore.add({
          type: 'error',
          text: translate('yiggle_form_wizard.export.error_no_id'),
        }, 6000);
        return;
      }

      const url = `/admin/api/fw/forms/${id}/submissions.csv`;

      this.setLoading(true);

      snackbarStore.add({
        type: 'info',
        text: translate('yiggle_form_wizard.export.preparing'),
      }, 4000);

      fetch(url, {
        method: 'GET',
        headers: {
          Accept: 'text/csv',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      })
        .then((response) => {
          if (!response.ok) {
            const ct = response.headers.get('content-type') || '';
            if (ct.includes('application/json')) {
              return response.json().then((data) => {
                throw new Error(data?.message || `HTTP ${response.status}`);
              });
            }
            return response.text().then((text) => {
              throw new Error(text || `HTTP ${response.status}`);
            });
          }

          const disposition = response.headers.get('Content-Disposition');
          const filename = parseFilenameFromContentDisposition(
            disposition,
            `wizard_${id}_submissions.csv`
          );

          return response.blob().then((blob) => ({blob, filename}));
        })
        .then(({blob, filename}) => {
          triggerBrowserDownload(blob, filename);

          snackbarStore.add({
            type: 'success',
            text: translate('yiggle_form_wizard.export.success'),
          }, 4000);
        })
        .catch((e) => {
          snackbarStore.add({
            type: 'error',
            text: `${translate('yiggle_form_wizard.export.error')} (${e.message})`,
          }, 6000);
        })
        .finally(() => {
          this.setLoading(false);
        });
    };
  }

  getToolbarItemConfig() {
    return {
      type: 'button',
      label: translate('yiggle_form_wizard.export.button_label'),
      icon: 'su-download',
      loading: this.loading,
      disabled: this.loading,
      onClick: this.handleClick,
    };
  }
}
