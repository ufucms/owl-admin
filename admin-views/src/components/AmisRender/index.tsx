import React from "react"
import "./style/index.less"
import {render as renderAmis} from "amis"
import {GlobalState} from "@/store"
import {useSelector} from "react-redux"
import {amisRequest} from "@/service/api"
import {ToastComponent} from "amis-ui"
import {useHistory} from "react-router"
import {registerCustomComponents} from "./CustomComponents"

registerCustomComponents()

const AmisRender = ({schema}) => {
    const history = useHistory()
    const {appSettings} = useSelector(({appSettings}: GlobalState) => ({appSettings}))

    const localeMap = {
        "zh_CN": "zh-CN",
        "en": "en-US"
    }

    const props = {
        locale: localeMap[appSettings?.locale || "zh_CN"] || "zh-CN",
    }

    const options = {
        fetcher: ({url, method, data}) => amisRequest(url, method, data),
        // eslint-disable-next-line @typescript-eslint/no-empty-function
        updateLocation: () => {
        },
        jumpTo: (location: string) => {
            if (location.startsWith("http") || location.startsWith("https")) {
                window.open(location)
            } else {
                history.push(location.startsWith("/") ? location : `/${location}`)
            }
        }
    }

    return (
        <div>
            <ToastComponent key="toast"></ToastComponent>
            {renderAmis(schema, props, options)}
        </div>
    )
}

export default AmisRender
