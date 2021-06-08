<template>
    <div>
        <div v-if="dokumen">
            <form class="d-block card mb-3"
                  @submit.prevent="onFormSubmit"
            >
                <div class="card-body">
                    <!-- Corrections / Recommendations -->
                    <div
                        v-if="Object.keys(this.tokenWithErrors).length > 0"
                        style="height: 200px; overflow-y: scroll"
                    >
                        <table
                            class="table table-borderless"
                            style="table-layout: fixed"
                        >


                            <tbody>

                            <template v-for="(tokenWithError, tokenString) in tokenWithErrors">
                                <tr :key="tokenString">
                                    <td class="font-weight-bold h5"
                                        colspan="3"
                                    >
                                        {{ tokenString }}
                                    </td>
                                </tr>
                                <tr v-for="(errorPosition, index) in tokenWithError.positions"
                                    :key="`${tokenString}-${index}`"
                                >
                                    <td class="d-flex justify-content-end">
                                        <button
                                            style="width: 100px"
                                            class="btn btn-dark btn-sm"
                                            type="button"
                                            @click="jumpIntoText(tokenWithError.index, errorPosition.index)"
                                        >
                                            #{{ index + 1 }}
                                        </button>
                                    </td>
                                    <td>
                                        <select
                                            v-model="errorPosition.selectedRecommendation"
                                            class="form-control form-control-sm"
                                            @change="errorPosition.correction = errorPosition.selectedRecommendation.word"
                                        >
                                            <option
                                                v-for="(recommendation, recIndex) in tokenWithError.recommendations"
                                                :key="recIndex"
                                                :value="recommendation"
                                            >
                                                {{ recommendation.word }} [{{ (recommendation.points * 100).toFixed(2) }}]
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <label :for="`input_correction_${tokenString}-${index}`"
                                               class="sr-only"
                                        >
                                            Correction
                                        </label>
                                        <input
                                            :id="`input_correction_${tokenString}-${index}`"
                                            v-model="errorPosition.correction"
                                            class="form-control form-control-sm"
                                            type="text"
                                        >
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-if="Object.keys(this.tokenWithErrors).length === 0"
                        class="alert alert-success"
                    >
                        Tidak terdapat kesalahan pengejaan sama sekali.
                    </div>

                    <!-- Progress bar -->
                    <div v-if="this.textProcessingProgress < 100"
                         class="my-2"
                    >
                        <p class="h5">
                            Memroses teks untuk memperoleh rekomendasi koreksi ({{ this.textProcessingProgress }}%)
                        </p>

                        <div class="progress">
                            <div :aria-valuenow="`${this.textProcessingProgress}%`"
                                 :style="{width: `${this.textProcessingProgress}%`}"
                                 aria-valuemax="100"
                                 aria-valuemin="0"
                                 class="progress-bar"
                                 role="progressbar"
                            > Processing...
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="Object.keys(this.tokenWithErrors).length > 0"
                    class="card-footer d-flex justify-content-end"
                >
                    <button class="btn btn-primary">
                        Perbarui
                    </button>
                </div>
            </form>

            <editor
                ref="vue_editor"
                v-model="dokumen.konten_html"
                :disabled="true"
                :init="{
                    menubar: false,
            content_style: '.has-spelling-error{text-decoration: underline;text-decoration-color: red; text-decoration-style: wavy; text-decoration-thickness: 1px; } .has-highlight { background-color: yellow; }',
         height: 640,
         plugins: [
           'advlist autolink lists link image charmap print preview anchor',
           'searchreplace visualblocks code fullscreen',
           'insertdatetime media table paste code help wordcount',
         ],
         toolbar: false,
       }"
                api-key="c3lgkroj62ttb5dfwxx5eeyc7cqkvqwjm6yyrpm0x8xypjnt"
                @onInit="onEditorInit"
            ></editor>
        </div>
        <div v-else>
            Loading...
        </div>
    </div>
</template>

<script>
import axios from "axios"
import tinymce from "tinymce"
import editor from "@tinymce/tinymce-vue"
import Swal from "sweetalert2"
import {chunk} from "lodash"
import SentenceTokenizer from "sentence-tokenizer"

export default {
    components: {
        editor: editor
    },

    props: {
        dataUrl: String,
        recommenderUrl: String,
        correctorUrl: String,
    },

    mounted() {
        axios.get(this.dataUrl)
            .then(response => {
                this.dokumen = response.data
            })
    },

    data() {
        return {
            dokumen: null,
            tokenWithErrors: {},
            processableTextPieces: [],
            processedTextPiecesCount: 0,
            tokenWithErrorIndexCounter: 0,
        }
    },

    computed: {
        textProcessingProgress() {
            return Math.round(this.processedTextPiecesCount / this.processableTextPieces.length * 100)
        },

        formData() {
            return {
                corrections: Object.keys(this.tokenWithErrors)
                    .map(token => ({
                        original: token,
                        replacements: this.tokenWithErrors[token]
                            .positions
                            .filter(errorPosition =>
                                (errorPosition.correction !== null) &&
                                (errorPosition.correction.length > 0)
                            )
                            .map(errorPosition => ({
                                index: errorPosition.index,
                                correction: errorPosition.correction,
                            }))
                    }))
                    .filter(correction => correction.replacements.length > 0)
            }
        }
    },

    methods: {
        jumpIntoText(tokenIndex, positionIndex) {
            let body = this.$refs.vue_editor.editor.getBody()
            let element = body
                .querySelector(`.has-spelling-error-${tokenIndex}-${positionIndex}`)

            element.scrollIntoView(false)

            for (const elem of body.querySelectorAll(".has-spelling-error")) {
                elem.classList.remove("has-highlight")
            }

            element.classList.add("has-highlight")
        },

        onFormSubmit() {
            Swal.fire({
                title: "Memroses Dokumen",
                text: "Dokumen sedang diproses, harap tunggu.",
                onBeforeOpen(popup) {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false
            })

            axios.post(this.correctorUrl, this.formData)
                .then(response => {
                    window.location.reload()
                }).catch(error => {
                    Swal.hideLoading()
                    Swal.close()
                    alert("Gagal merevisi dokumen.")
                })
        },

        markTokensThatHasSpellingErrorMultiple: function (tokenStrings) {
            const markerClass = "has-spelling-error"
            let editor = this.$refs.vue_editor.editor
            let editorBody = editor.getBody()
            let replacementList = []

            let indexCounters = {}
            for (let tokenString of tokenStrings) {
                indexCounters[tokenString] = 0
            }

            this.walkNodeTree(editorBody, (node) => {
                if (node.nodeType === Node.TEXT_NODE) {
                    let text = node.textContent

                    let matches = []
                    tokenStrings.forEach(tokenString => {
                        let regExp = RegExp(`(?<=[\\s,.:;"']|^)${tokenString}(?=[\\s,.:;"']|$)`, "gmu")
                        
                        for (let regExpMatchArray of text.matchAll(regExp)) {
                            this.tokenWithErrors[tokenString].positions.push({
                                index: indexCounters[tokenString],
                                selectedRecommendation: this.tokenWithErrors[tokenString].recommendations[0],
                                correction: null,
                            })

                            matches.push({
                                regexMatchArray: regExpMatchArray,
                                index: indexCounters[tokenString],
                                tokenIndex: this.tokenWithErrors[tokenString].index
                            })

                            ++indexCounters[tokenString]
                        }
                    })

                    /* Sort matches by index */
                    matches = matches.sort((matchA, matchB) => matchA.regexMatchArray.index - matchB.regexMatchArray.index)

                    let prevTextPos = 0
                    let documentFragment = document.createDocumentFragment()

                    matches.forEach(match => {
                        documentFragment.appendChild(
                            document.createTextNode(text.slice(prevTextPos, match.regexMatchArray.index))
                        )

                        /* Spelling error */
                        let spellingErrorSpanNode = document.createElement('span')
                        spellingErrorSpanNode.appendChild(document.createTextNode(match.regexMatchArray[0]))
                        spellingErrorSpanNode.classList.add(`${markerClass}`)
                        spellingErrorSpanNode.classList.add(`${markerClass}-${match.tokenIndex}-${match.index}`)
                        documentFragment.appendChild(spellingErrorSpanNode)

                        prevTextPos = match.regexMatchArray.index + match.regexMatchArray[0].length
                    })

                    documentFragment.appendChild(
                        document.createTextNode(text.slice(prevTextPos))
                    )

                    replacementList.push({
                        original: node,
                        replacement: documentFragment,
                    })
                }
            })

            chunk(replacementList, 100)
                .forEach(replacementListChunk => {
                    replacementListChunk.forEach(pair => {
                        pair.original.parentNode.replaceChild(
                            pair.replacement,
                            pair.original,
                        )
                    })
                })
        },

        walkNodeTree(node, callback) {
            node.childNodes.forEach(childNode => {
                callback(childNode)
                this.walkNodeTree(childNode, callback)
            })
        },

        getProcessableTextPieces: function (editor) {
            let text = "";
            let walker = new tinymce.dom.TreeWalker(editor.dom.getRoot());

            do {
                let node = walker.current()
                if (node.nodeType === Node.TEXT_NODE) {
                    text += node.textContent
                } else {
                    if ((node.nodeName === "P") || (node.nodeName === "BR"))
                    text += ". "
                }
            } while (walker.next());
            
            const tokenizer = new SentenceTokenizer()
            tokenizer.setEntry(text)
    
            let sentences = tokenizer
                .getSentences()
                .map(sentence => sentence.replaceAll(/\p{P}/gu, ' '))
                .map(sentence => sentence.trim())
                .filter(sentence => sentence.length > 0)
                .map(sentence => sentence.split(/[\p{Z}\/-]+/gmu));
    
    
            console.log(sentences)
            
            return sentences
        },

        onEditorInit(e) {
            let editor = e.target
            this.processableTextPieces = this.getProcessableTextPieces(editor)
            this.fetchRecommendationsFromServer()
        },

        getSpellingRecommendations(tokens) {
            return axios.post(this.recommenderUrl, {
                tokens: tokens
            })
        },

        async fetchRecommendationsFromServer() {
            for (const textTokens of this.processableTextPieces) {
                const tokens = textTokens
                    .filter(token => !this.tokenWithErrors.hasOwnProperty(token.toLowerCase()))

                const recommendationData = await this.getSpellingRecommendations(tokens)

                recommendationData.data.forEach(recommendationDatum => {
                    if (this.tokenWithErrors.hasOwnProperty(recommendationDatum.token)) {
                        return;
                    }

                    this.$set(this.tokenWithErrors, recommendationDatum.token, {
                        index: this.tokenWithErrorIndexCounter++,
                        positions: [],
                        correction: null,
                        selectedRecommendation: recommendationDatum.recommendations[0],
                        recommendations: recommendationDatum.recommendations
                    })
                })
                ++this.processedTextPiecesCount
            }

            this.markTokensThatHasSpellingErrorMultiple(Object.keys(this.tokenWithErrors))
        },
    }
}
</script>
