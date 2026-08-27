import SelectState, {PAGE_NOT_SELECTED, PAGE_SELECTED} from './SelectState';


export default class SelectPlugin {

    constructor() {
        this.state = new SelectState()

        this.$togglePageCheckbox = null
        this.$unselectAllbtn = null
        this.$selectPageBtn = null
        this.$unselectPageBtn = null
        this.$selectInfo = null

    }

    /**
     * @param {PonchoDataTable} ponchoDatatable
     */
    configure(ponchoDatatable) {
        this.ponchoDatatable = ponchoDatatable

        this.$togglePageCheckbox = this.ponchoDatatable.querySelector('.js-toggle-select-page')
        this.$unselectAllbtn = this.ponchoDatatable.querySelector('.js-unselect-all')
        this.$selectPageBtn = this.ponchoDatatable.querySelector('.js-select-page')
        this.$unselectPageBtn = this.ponchoDatatable.querySelector('.js-unselect-page')
        this.$selectInfo = this.ponchoDatatable.querySelector('.js-select-info')

        this._initToolbar()

        this.ponchoDatatable.datatable.on('draw', () => this._onDraw())

        // register api
        this.ponchoDatatable.selectPage = this.selectPage.bind(this)
        this.ponchoDatatable.unselectPage = this.unselectPage.bind(this)
        this.ponchoDatatable.unselectAll = this.unselectAll.bind(this)
        this.ponchoDatatable.getSelectedIds = this.getSelectedIds.bind(this)

        // override api
        this.ponchoDatatable.getState = () => {
            let state = this.ponchoDatatable._getCurrentState()
            state['ids'] = this.getSelectedIds()
            state['count']['selected'] = state['ids'].length
            return state
        }
    }

    // Api
    selectPage() {
        this.state.pageIds.forEach(({$row, $input}, id) => {
            this.state.addSelectedId(id)
            this._updateRowView($row, $input, true)
        })
        this.state.setPageState(PAGE_SELECTED)
        this._updateView()
    }

    unselectPage() {
        this.state.pageIds.forEach(({$row, $input}, id) => {
            this.state.removeSelectedId(id)
            this._updateRowView($row, $input, false)
        })
        this.state.setPageState(PAGE_NOT_SELECTED)
        this._updateView()
    }

    unselectAll() {
        this.state.pageIds.forEach(({$row, $input}, id) => {
            this._updateRowView($row, $input, false)
        })

        this.state.clearSelection()
        this._updateView()
    }

    getSelectedIds() {
        return Array.from(this.state.ids)
    }

    clear() {
        this.state.clearSelection()
        this.state.clearPageIds()
    }

    // Helper

    _onDraw() {
        this.state.clearPageIds()

        // init rows
        this.ponchoDatatable.tbody.querySelectorAll('tr').forEach($row => {
            const selectable = $row.dataset.select !== 'false'

            if (selectable) {
                this._initRow($row)
            }
        })

        // update view
        this._updateView()
    }

    _initToolbar() {
        this.$togglePageCheckbox.addEventListener('change', () => {
            if (PAGE_NOT_SELECTED === this.state.getPageState()) {
                this.selectPage()
            } else {
                this.unselectPage()
            }
        })

        this.$unselectAllbtn.addEventListener('click', evt => {
            evt.preventDefault()
            this.unselectAll()
        })
        this.$selectPageBtn.addEventListener('click', evt => {
            evt.preventDefault()
            this.selectPage()
        })
        this.$unselectPageBtn.addEventListener('click', evt => {
            evt.preventDefault()
            this.unselectPage()
        })
    }

    _initRow($row) {
        const id = $row.dataset.id
        const $input = $row.querySelector('.js-toggle-select input')

        // fake row => skip
        if (!id || !$input) {
            return
        }

        this.state.addPageId(id, {$row, $input})

        // row is currently selected
        if (this.state.isIdSelected(id)) {
            this._updateRowView($row, $input, true)
        }

        // bind toggle event
        $input.addEventListener('click', () => {
            if (this.state.isIdSelected(id)) {
                this.state.removeSelectedId(id)
                this._updateRowView($row, $input, false)
            } else {
                this.state.addSelectedId(id)
                this._updateRowView($row, $input, true)
            }

            this._updateView()
        })
    }

    _updateView() {
        const c = this.state.count()

        if (c === 0) {
            this.$selectInfo.textContent = poncho.translator.trans('datatable.no_item_selected')
        } else if (c === 1) {
            this.$selectInfo.textContent = poncho.translator.trans('datatable.one_item_selected')
        } else {
            this.$selectInfo.textContent = poncho.translator.trans('datatable.many_item_selected', {'{c}': c})
        }

        this.$unselectAllbtn.hidden = c === 0

        this.ponchoDatatable.querySelectorAll('[data-display=selection]').forEach($elt => $elt.hidden = c === 0)
        this.ponchoDatatable.querySelectorAll('[data-display=no_selection]').forEach($elt => $elt.hidden = c > 0)

        const pageState = this.state.getPageState()

        if (pageState === PAGE_SELECTED) {
            this.$togglePageCheckbox.checked = true
            this.$togglePageCheckbox.indeterminate = false

            this.$selectPageBtn.hidden = true
            this.$unselectPageBtn.hidden = false

        } else if (pageState === PAGE_NOT_SELECTED) {
            this.$togglePageCheckbox.checked = false
            this.$togglePageCheckbox.indeterminate = false

            this.$selectPageBtn.hidden = false
            this.$unselectPageBtn.hidden = true

        } else {
            this.$togglePageCheckbox.indeterminate = true

            this.$selectPageBtn.hidden = false
            this.$unselectPageBtn.hidden = false
        }
    }


    _updateRowView($row, $input, selected = true) {
        if (selected) {
            $row.classList.add('selected')
            $input.checked = true
        } else {
            $row.classList.remove('selected')
            $input.checked = false
        }
    }

}
