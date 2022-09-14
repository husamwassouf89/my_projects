import React, { useEffect, useState } from 'react'

import { FilePond, registerPlugin } from 'react-filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'
import FilePondPluginImageTransform from 'filepond-plugin-image-transform'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import FilePondPluginImageCrop from 'filepond-plugin-image-crop';
// import FilePondPluginGetFile from 'filepond-plugin-get-file';
// import 'filepond-plugin-get-file/dist/filepond-plugin-get-file.min.css';
// import FilePondPluginMediaPreview from 'filepond-plugin-media-preview';
// import 'filepond-plugin-media-preview/dist/filepond-plugin-media-preview.min.css';
import FilePondPluginImageEditor from 'filepond-plugin-image-edit';

import ar from './ar'
import { setOptions } from 'filepond';

import './FileUploader.css'

// Doka
import * as Doka from '../../react_doka/doka.esm.min.js';
import { useCookies } from 'react-cookie';

registerPlugin(FilePondPluginImagePreview)
registerPlugin(FilePondPluginFileValidateType)
registerPlugin(FilePondPluginImageTransform)
registerPlugin(FilePondPluginImageCrop)
// registerPlugin(FilePondPluginGetFile)
// registerPlugin(FilePondPluginMediaPreview)
registerPlugin(FilePondPluginImageEditor)

interface fileUploaderProps {
    type: "pd" | "clients" | "attachments";
    default?: string;
    onStartUploading(): any;
    onErrorUploading(): any;
    onUpload(file_object: any): any;
    onRemove(): any;
    avatar?: boolean;
    required?: boolean;
    labelIdle?: string;
    allowImageEdit?: boolean;
    cropOptions?: any;
    allowMultiple?: boolean;
}

export default (props: fileUploaderProps) => {

    const isFacebookApp = () => {
        if(typeof navigator === "undefined")
            return
        var ua = navigator.userAgent || navigator.vendor;
        return (ua.indexOf("FBAN") > -1) || (ua.indexOf("FBAV") > -1);
    }

    const [labelError, setLabelError] = useState<string>("Error during upload")

    let files: any = props.default ? { files: [{ source: props.default, options: { type: "local" } }] } : undefined
    let avatarFields: any = props.avatar ? {
        className: "avatar_uploader",
        stylePanelLayout: "circle",
        styleLoadIndicatorPosition: "center top",
        styleButtonRemoveItemPosition: "center top",
        styleButtonProcessItemPosition: "center top",
        styleProgressIndicatorPosition: "center top",
        imagePreviewHeight: 150,
        imagePreviewWidth: 150,
        imageCropAspectRatio: '1:1',
        imageResizeTargetWidth: 200,
        imageResizeTargetHeight: 200,
    } : undefined

    let labelIdle: any = props.labelIdle ? {
        labelIdle: props.labelIdle
    } : undefined

    useEffect(() => {
        const lang = typeof localStorage !== 'undefined' ? localStorage.getItem('lang') : 'en';
        if(lang === 'ar')
            setOptions(ar)
    })

    const [cookies, _, removeCookie] = useCookies();

    return (
        <FilePond allowMultiple={props.allowMultiple} allowImageEdit={props.allowImageEdit} imageEditInstantEdit={true} imageEditEditor={Doka.create( props.cropOptions || {})} required={props.required} {...files} {...avatarFields} {...labelIdle} onremovefile={(error, file) => { if(props.onRemove) props.onRemove() }} labelFileProcessingError={labelError} server={
            {
                timeout: 7000,
                load: (source, load, error, progress, abort, headers) => {
                    var myRequest = new Request(source);
                    fetch(myRequest).then(function(response) {
                        response.blob().then(function (myBlob) {
                            load(myBlob)
                        });
                    });         
                },
                remove: (source, load, error) => {
                    if(props.onRemove)
                        props.onRemove()
                    load()
                },
                process: (fieldName, file, metadata, load, error, progress, abort) => {
                    // let mime_type = file.type.split('/')[0]
                    // mime_type = file.type === "application/msword" ||  file.type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document" ? "word" : mime_type
                    // let file_type: file_type = mime_type === 'image' ? "image" : mime_type === "video" ? "video" : file.type === "application/pdf" ? "pdf" : mime_type === "word" ? "doc" : "other"
                    
                    // if(!props.type.includes(file_type)) {
                    //     setLabelError("Invalid file type please select (" + props.type.join(" - ") + ")")
                    //     error(labelError)
                    //     return {
                    //         abort: () => {
                    //             abort();
                    //         }
                    //     };
                    // }

                    if(props.onStartUploading)
                        props.onStartUploading()

                    const formData = new FormData();
                    formData.append("type", props.type || "");
                    formData.append("file", file, file.name);

                    const request = new XMLHttpRequest();
                    let url = 'https://ifrs.opalcityadvisory.com/api/public/'
                    url += "help/upload-attachments"
                    request.open('POST', url);

                    request.setRequestHeader("Authorization", cookies?.userinfo?.accessToken)
                    request.upload.onprogress = (e) => {
                        progress(e.lengthComputable, e.loaded, e.total);
                    };

                    request.onload = function () {
                        if (request.status >= 200 && request.status < 300) {
                            if(props.onUpload)
                                props.onUpload(JSON.parse(request.response))
                            load(request.responseText);
                        }
                        else {
                            setLabelError("Error during upload")
                            error(labelError);
                            if(props.onErrorUploading)
                                props.onErrorUploading()
                        }
                    };

                    request.send(formData);

                    return {
                        abort: () => {
                            request.abort();

                            abort();
                        }
                    };
                }
            }
        } />
    )
}