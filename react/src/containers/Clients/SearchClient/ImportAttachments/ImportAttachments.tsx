import { useState } from "react";
import FileUploader from "../../../../components/FileUploader/FileUploader";
import { WhiteboxLoader } from "../../../../components/Loader/Loader";
import Modal from "../../../../components/Modal/Modal";
import API from "../../../../services/api/api";

export default (props: { client_id: number; open: boolean; toggle(): any; }) => {
  const [attachments, setAttachments] = useState<number[]>([])
  const [isLoading, setIsLoading] = useState<boolean>(false);

  // API
  const ENDPOINTS = new API()

  const save = () => {
    if(attachments.length === 0)
      return;
    setIsLoading(true);
    ENDPOINTS.clients().add_attachments({ id: props.client_id, attachment_ids: attachments })
    .then(() => {
      window.location.reload()
    })
  }

  return(
    <Modal open={props.open} toggle={props.toggle}>
      <div style={{ width: 500 }}>
        { isLoading && <WhiteboxLoader /> }
        <FileUploader
          type="attachments"
          allowMultiple={true}
          // labelIdle="Drag & Drop Files or Click to select one"
          onStartUploading={() => {}}
          onErrorUploading={() => {}}
          onRemove={() => {}}
          onUpload={(file) => {
              setAttachments(prevAttachments => [ ...prevAttachments, file.data[0] ])
          }} />
        <div className="margin-top-20">
          <button className="button bg-gold color-white" onClick={save}>Add Attachments</button>
        </div>
      </div>
    </Modal>
  );
}