<template>
    <div>
        <h1> Korektor Ejaan</h1>

        <div class="card">
            <div class="card-body">
                <form @submit.prevent="onFormSubmit">
                    <div class="form-group">
                        <label for="text">
                            Teks untuk Diperiksa:
                        </label>
                        <textarea
                            class="form-control"
                            id="text"
                            v-model="text"
                            placeholder="Masukkan teks yang hendak diperiksa disini"
                            rows="20"
                        ></textarea>
                    </div>

                    <div class="form-group d-flex justify-content-end">
                        <button class="btn btn-primary">
                            Periksa Kesalahan Pengejaan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="row mt-3"
                 v-if="this.tokens.length !== 0">
                <div style="font-size: 14pt"
                     class="card col-md-7">
                    <div class="card-body">
                    <span v-for="token in tokens"
                          @click="onTokenClick(token)"
                          :class="{
                            'badge': token.incorrect,
                            'badge-info': token.incorrect && token.id === get(selectedToken, 'id', false),
                            'badge-danger': token.incorrect && token.pickedCorrection === null && token.id !== get(selectedToken, 'id', false),
                            'badge-success': token.incorrect && token.pickedCorrection !== null && token.id !== get(selectedToken, 'id', false),
                      }"
                    > {{ tokenDisplay(token) }} </span>
                    </div>
                </div>

                <div class="card col-md-5">
                    <div class="card-body">
                        <template v-if="selectedToken !== null">
                            <h5> Koreksi untuk "{{ selectedToken.cleaned }}"</h5>

                            <button @click="onRevertButtonClick"
                                    class="d-block w-100 btn btn-dark btn-sm my-1">
                                Kembalikan ke Bentuk Semula
                            </button>

                            <div class="row">
                              <div class="col">
                                <h3> Rekomendasi Koreksi </h3>
                                <ul class="list-group">
                                  <li
                                      @click="onCorrectionOptionClick(correction)"
                                      class="list-group-item list-group-item-action"
                                      :class="{ active: selectedToken.pickedCorrection === correction}"
                                      v-for="correction in selectedToken.corrections"
                                  > {{ correction }}
                                  </li>
                                </ul>
                              </div>
                            </div>

                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Swal from 'sweetalert2'
    import {get} from 'lodash'

    export default {
        data() {
            return {
                text: null,
                tokens: [],
                selectedToken: null,
            }
        },

        methods: {
            get: get,
            onFormSubmit() {
                this.provideCorrections(this.text)
            },

            tokenDisplay(token) {
                if (token.pickedCorrection === null) {
                    return token.original
                }

                let uppercaseMask = []
                for (let i = 0; i < token.original.length; ++i) {
                    uppercaseMask.push(
                        token.original[i] === token.original[i].toUpperCase()
                    )
                }

                let replaced = token.original.toLowerCase().replace(token.cleaned, token.pickedCorrection)
                let result = ""

                for (let i = 0; i < replaced.length; ++i) {
                    result += (uppercaseMask[i] ?? false) ?
                        replaced[i].toUpperCase() : replaced[i]
                }

                return result
            },

            onTokenClick(token) {
                if (token.incorrect) {
                    this.selectedToken = token
                } else {
                    this.selectedToken = null
                }
            },

            onRevertButtonClick() {
                this.selectedToken.pickedCorrection = null
            },

            onCorrectionOptionClick(correction) {
                this.tokens = this.tokens.map(tok => {
                    if (this.selectedToken.id === tok.id) {
                        this.selectedToken = {...tok, pickedCorrection: correction}
                        return this.selectedToken
                    }
                    return tok;
                })
            },

            provideCorrections(text) {
                Swal.showLoading()

                axios.post('/', {text: text})
                    .then(response => {
                        Swal.close()
                        Swal.hideLoading()

                        this.tokens = response.data.map((token, index) => ({
                            ...token,
                            id: index,
                            pickedCorrection: null,
                        }))
                    })
                    .catch(error => {
                        Swal.close()
                        Swal.hideLoading()

                        console.log(error)
                    })
            }
        },
    }
</script>
