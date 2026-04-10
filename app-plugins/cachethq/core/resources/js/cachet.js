import Chart from 'chart.js/auto'
import 'chartjs-adapter-moment'

import Alpine from 'alpinejs'

import Anchor from '@alpinejs/anchor'
import Collapse from '@alpinejs/collapse'
import Focus from '@alpinejs/focus'
import Ui from '@alpinejs/ui'

Chart.defaults.color = '#fff'
window.Chart = Chart

const hasExistingAlpine = typeof window.Alpine !== 'undefined'
const alpine = window.Alpine ?? Alpine

alpine.plugin(Anchor)
alpine.plugin(Collapse)
alpine.plugin(Focus)
alpine.plugin(Ui)

window.Alpine = alpine

if (! hasExistingAlpine) {
	alpine.start()
}
