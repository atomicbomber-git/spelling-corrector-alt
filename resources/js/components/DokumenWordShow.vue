<template>
    <div>
        <div v-if="dokumen">
            <form class="d-block card mb-3"
                  @submit.prevent="onFormSubmit"
            >
                <div class="card-header">
                    Rekomendasi Koreksi Ejaan
                </div>

                <div class="card-body">
                    <!-- Corrections / Recommendations -->
                    <div
                        v-if="Object.keys(this.tokenWithErrors).length > 0"
                        style="height: 200px; overflow-y: scroll"
                    >
                        <table
                            class="table table-sm table-striped"
                            style="table-layout: fixed"
                        >
                            <thead class="thead thead-dark">
                            <tr>
                                <th style="width: 100%"> Kata</th>
                                <th style="width: 100%"> Rekomendasi</th>
                                <th style="width: 100%"> Koreksi</th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr
                                v-for="(tokenWithError, tokenString) in tokenWithErrors"
                                :key="tokenString"
                            >
                                <td> {{ tokenString }}</td>
                                <td>
                                    <select
                                        v-model="tokenWithError.selectedRecommendation"
                                        class="form-control form-control-sm"
                                        @change="onCorrectionRecommendationChange(tokenWithError)"
                                    >
                                        <option
                                            v-for="(recommendation, recIndex) in tokenWithError.recommendations"
                                            :key="recIndex"
                                            :value="recommendation"
                                        >
                                            {{ recommendation }}
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <label :for="`input_correction_${tokenString}`"
                                           class="sr-only"
                                    >
                                        Correction
                                    </label>
                                    <input
                                        :id="`input_correction_${tokenString}`"
                                        v-model="tokenWithError.correction"
                                        class="form-control form-control-sm"
                                        type="text"
                                    >
                                </td>
                            </tr>
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
            content_style: '.has-spelling-error{text-decoration: underline;text-decoration-color: red;}',
         height: 640,
         plugins: [
           'advlist autolink lists link image charmap print preview anchor',
           'searchreplace visualblocks code fullscreen',
           'insertdatetime media table paste code help wordcount',
           'textcolor',
         ],
         toolbar: null,
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
import {chunk} from "lodash"

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
        }
    },

    computed: {
        textProcessingProgress() {
            return Math.round(this.processedTextPiecesCount / this.processableTextPieces.length * 100)
        },

        formData() {
            return {
                correction_list: Object.keys(this.tokenWithErrors)
                    .map(token => ({
                        original: token,
                        replacement: this.tokenWithErrors[token].correction,
                    }))
            }
        }
    },

    methods: {
        onFormSubmit() {
            axios.post(this.correctorUrl, this.formData)
                .then(response => {
                    window.location.reload()
                }).catch(error => {
                alert("Gagal merevisi dokumen.")
            })
        },

        isValidLeftDelimiter(character) {
            return [' ', '"', '>', '\'', '(', ':'].includes(character)
        },

        isValidRightDelimiter(character) {
            return [' ', '"', '.', ',', '\'', ')', ':', '<', '!', '?'].includes(character)
        },

        markTokensThatHasSpellingError: function (editor, token) {
            const markerClass = "has-spelling-error"
            let editorContent = editor.getContent()
            let tokenPos = editorContent.toLowerCase().indexOf(token.toLowerCase())
            while (tokenPos !== -1) {
                if (
                    (tokenPos > 0 && this.isValidLeftDelimiter(editorContent[tokenPos - 1])) &&
                    (tokenPos < (editorContent.length - 1) && this.isValidRightDelimiter(editorContent[tokenPos + token.length]))
                ) {
                    /* If the token is actually surrounded by whitespaces on both sides, treat it as a proper
                    *  token and proceed to mark it using <span> tags
                    * */
                    let tokenAsInContent = editorContent.slice(tokenPos, tokenPos + token.length)
                    let replacement = `<span class="${markerClass}"> ${tokenAsInContent} </span>`
                    let contentLowerHalf = editorContent.slice(0, tokenPos)
                    let contentUpperHalf = editorContent.slice(tokenPos + token.length)
                    let newEditorContent = contentLowerHalf + replacement + contentUpperHalf
                    editor.setContent(newEditorContent)
                    editorContent = newEditorContent
                    tokenPos = editorContent.toLowerCase().indexOf(
                        token.toLowerCase(),
                        tokenPos + replacement.length
                    )
                } else {
                    /*
                    * Else if the token is not surrounded by whitespaces on both sides,
                    * don't treat it as a token & move on to the next possible token
                    * */

                    tokenPos = editorContent.toLowerCase().indexOf(
                        token.toLowerCase(),
                        tokenPos + 1
                    )
                }
            }
        },

        getProcessableTextPieces: function (editor) {
            let processableTextPieces = []
            let walker = new tinymce.dom.TreeWalker(editor.dom.getRoot());

            do {
                let node = walker.current()
                if (node.nodeType !== Node.TEXT_NODE) {
                    continue
                }
                if (node.parentNode.classList.contains("has-spelling-error")) {
                    continue
                }

                let parts = node.textContent.split(' ')
                parts.forEach(part => {
                    processableTextPieces.push(part)
                })
            } while (walker.next());

            return processableTextPieces
        },


        onEditorInit(e) {
            let editor = e.target
            this.processableTextPieces = chunk(this.getProcessableTextPieces(editor), 30)
            this.fetchRecommendationsFromServer()
        },

        getSpellingRecommendations(text) {
            return axios.post(this.recommenderUrl, {
                text: text
            })
        },

        onCorrectionRecommendationChange(tokenWithError) {
            tokenWithError.correction = tokenWithError.selectedRecommendation
        },

        async fetchRecommendationsFromServer() {
            for (const textTokens of this.processableTextPieces) {
                const text = textTokens
                    .map(token => token
                        .replace(new RegExp("[^\\w]*$", "gm"), '')
                        .replace(new RegExp("^[^\\w]*", "gm"), '')
                    )
                    .filter(token => token.length > 1)
                    .filter(token => !this.tokenWithErrors.hasOwnProperty(token.toLowerCase())
                    ).join(' ')

                const recommendationData = await this.getSpellingRecommendations(text)

                recommendationData.data.forEach(recommendationDatum => {
                    if (this.tokenWithErrors.hasOwnProperty(recommendationDatum.token)) {
                        return;
                    }

                    this.$set(this.tokenWithErrors, recommendationDatum.token, {
                        correction: recommendationDatum.recommendations[0],
                        selectedRecommendation: recommendationDatum.recommendations[0],
                        recommendations: recommendationDatum.recommendations
                    })

                    this.markTokensThatHasSpellingError(
                        this.$refs.vue_editor.editor,
                        recommendationDatum.token,
                    )
                })
                ++this.processedTextPiecesCount
            }
        }
    }
}
</script>
