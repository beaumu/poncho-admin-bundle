export default class RowDetailsPlugin {

    constructor() {
        this.ponchoDatatable = null
    }

    /**
     * @param {PonchoDataTable} ponchoDatatable
     */
    configure(ponchoDatatable) {
        this.ponchoDatatable = ponchoDatatable

        this.ponchoDatatable.datatable.on('draw', () => {
            this.ponchoDatatable.tbody.querySelectorAll('.js-toggle-child-row-btn').forEach($btn => this._bind($btn))
        })

    }

    _bind($btn) {
        $btn.addEventListener('click', evt => {
            evt.preventDefault()
            const $row = $btn.closest('tr')
            const row = this.ponchoDatatable.datatable.row($row)

            const html = $btn.firstElementChild.innerHTML

            if (row) {
                if (row.child.isShown()) {
                    row.child.hide()
                    $btn.classList.add('collapsed');
                } else {
                    row.child(html)
                    row.child.show()
                    $btn.classList.remove('collapsed');
                }
            }
        })
    }


}
